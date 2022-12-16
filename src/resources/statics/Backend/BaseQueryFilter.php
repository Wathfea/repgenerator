<?php

namespace App\Abstraction\Filter;

use App\Domain\Locale\Models\Locale;
use App\Domain\Locale\Services\LocaleService;
use App\Domain\Translation\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Kblais\QueryFilter\QueryFilter;

/**
 * Class BaseQueryFilter.
 */
class BaseQueryFilter extends QueryFilter
{
    CONST PER_PAGE = 'per_page';
    CONST SEARCH_DATE = 'date';
    CONST SEARCH_ID = 'id';
    CONST SEARCH_BOOLEAN = 'boolean';
    CONST SEARCH_FILTER_OVERRIDE = 'filter_override';
    CONST SEARCH_TYPES = [
        self::SEARCH_DATE,
        self::SEARCH_ID,
        self::SEARCH_BOOLEAN,
        self::SEARCH_FILTER_OVERRIDE
    ];

    /**
     * @var array
     */
    protected array $searchableColumns = [];

    /**
     * @var array
     */
    protected array $sortOverrides = [];

    /**
     * @param  array  $overrides
     * @return $this
     */
    public function addSortOverrides(array $overrides): self
    {
        foreach ($overrides as $key => $override) {
            $this->addSortOverride($key, $override);
        }
        return $this;
    }

    /**
     * @param  string  $name
     * @param  array  $override
     * @return $this
     */
    public function addSortOverride(string $name, array $override): self
    {
        $this->sortOverrides[$name] = $override;

        return $this;
    }

    /**
     * @param  Builder  $builder
     * @return LengthAwarePaginator|Builder|Builder[]|Collection
     */
    public function apply(Builder $builder): LengthAwarePaginator|Builder|Collection|array
    {
        $this->builder = $builder;

        foreach ($this->filters as $name => $value) {
            $methodName = Str::camel($name);

            if ($this->shouldCallMethod($methodName, $value)) {
                $this->{$methodName}($value);
            }
        }

        return $this->builder;
    }

    /**
     * @return array
     */
    public function getSortOverrides(): array
    {
        return $this->sortOverrides;
    }

    /**
     * @param  int  $id
     * @return Builder
     */
    public function id(int $id): Builder
    {
        return $this->builder->where('id', $id);
    }


    /**
     * @param  Builder  $builder
     * @param  string  $column
     * @param  string  $value
     */
    private function globalSearchColumn(Builder $builder, string $column, string $value)
    {
        $builder->orWhere($column, 'like', '%'.$value.'%');
    }

    public function recursiveSearch(Builder $builder, $value, $index, $search) {
        if (is_array($value)) {
            $builder->orWhereHas($index, function (Builder $relationBuilder) use ($value, $search) {
                $relationBuilder->where(function (Builder $qb) use ($value, $search) {
                    foreach ($value as $innerIndex => $column) {
                        if (!is_array($column) ) {
                            $this->globalSearchColumn($qb, $column, $search);
                        } else {
                            $this->recursiveSearch($qb, $column, $innerIndex, $search);
                        }
                    }
                });
            });
        } else if ( !in_array($value, static::SEARCH_TYPES) ) {
            $this->globalSearchColumn($builder, $value, $search);
        } else if ( $value == static::SEARCH_FILTER_OVERRIDE ) {
            $methodName = lcfirst(str_replace('_', '', ucwords($index, '_')));
            if ( method_exists($this, $methodName) ) {
                $this->$methodName($search, true);
            }
        }
    }

    /**
     * @param  string  $search
     */
    public function search(string $search): void
    {
        $this->builder->where(function (Builder $builder) use ($search) {
            foreach ($this->getSearchableColumns() as $index => $value) {
                $this->recursiveSearch($builder, $value, $index, $search);
            }
        });
    }

    /**
     * @param Builder $builder
     * @param $type
     * @param $columnName
     * @param $acceptedValue
     * @return void
     */
    private function searchType(Builder $builder, $type, $columnName, $acceptedValue): void
    {
        switch($type) {
            case self::SEARCH_DATE:
                $dates = array_filter(array_map(function($dateString) {
                    return strlen($dateString) > 0 ? date($dateString) : null;
                }, explode(',', $acceptedValue)));
                if ( count($dates) > 1 && count(array_unique($dates)) > 1 ) {
                    $builder->whereBetween($columnName,$dates);
                } else {
                    $builder->whereDate($columnName,$dates[0]);
                }
                break;
            case self::SEARCH_ID:
            case self::SEARCH_BOOLEAN:
                $builder->where($builder->from . '.' . $columnName, $acceptedValue);
                break;
            default:
                $values = explode(',', $acceptedValue);
                if ( count($values) > 1 ) {
                    $builder->whereIn($builder->from . '.' . $columnName, $values);
                } else {
                    $builder->where($builder->from . '.' . $columnName, 'like', '%'. $acceptedValue .'%');
                }
                break;
        }
    }

    /**
     * @param Builder $builder
     * @param $columnOrRelation
     * @param $typeOrSearch
     * @param $acceptedValue
     * @return void
     */
    private function searchColumn(Builder $builder, $columnOrRelation, $typeOrSearch, $acceptedValue): void
    {
        if ( is_array($typeOrSearch) ) {
            $relationSearch = explode('.', $columnOrRelation);
            $relationName = $relationSearch[0];
            if ( count($relationSearch) > 1 ) {
                $columnName = $relationSearch[1];
            } else {
                $columnName = $relationName;
            }
            $builder->whereHas($relationName, function (Builder $relationBuilder) use ($typeOrSearch, $acceptedValue, $columnName) {
                foreach ( $typeOrSearch as $relationKey => $relationValue) {
                    $isSearch = is_array($relationValue);
                    if ( !$isSearch && $relationValue !== $columnName ) {
                        continue;
                    }
                    $this->searchColumn($relationBuilder, $isSearch ? $relationKey : $relationValue, $isSearch ? $relationValue : null, $acceptedValue);
                }
            });
        } else {
            $this->searchType($builder, $typeOrSearch, $columnOrRelation, $acceptedValue);
        }
    }

    /**
     * @param string|array $columns
     * @return void
     */
    public function searchColumns(string|array $columns): void {
        if ( !is_array($columns) ) {
            $columns = [$columns];
        }
        foreach ( $columns as $search ) {
            $columnData = explode(':', $search);
            $columnName = explode('.', $columnData[0])[0];
            if ( count($columnData ) < 2 ) {
                continue;
            }
            $acceptedValue = $columnData[1];
            if ( !str_contains($columnName,'_id') && str_contains($columnName, 'translation_') ) {
                $localeCode = explode('_', $columnName)[1];
                /** @var LocaleService $localeService */
                $localeService = app(LocaleService::class);
                /** @var Locale $locale */
                $locale = $localeService->getRepositoryService()->getByCode2($localeCode);
                if ( $locale ) {
                    $this->builder->whereHas('translations', function (Builder $translationBuilder) use($locale, $acceptedValue) {
                        $translationBuilder->where(Translation::LOCALE_ID_COLUMN,$locale->getAttribute(Locale::ID_COLUMN))
                            ->where('value', 'like', '%'. $acceptedValue .'%');
                    });
                }
                continue;
            }
            $methodName = lcfirst(str_replace('_', '', ucwords($columnName, '_')));
            if ( method_exists($this, $methodName) ) {
                $this->$methodName($acceptedValue);
            } else {
                foreach ( $this->getSearchableColumns() as $index => $value ) {
                    $isSearching = false;
                    if ( is_array($value) && $index == $columnName ) {
                        $isSearching = true;
                    } else {
                        $compare = $value;
                        if ( in_array($value, static::SEARCH_TYPES) ) {
                            $compare = $index;
                        }
                        $isSearching = $compare == $columnName;
                    }
                    if ( $isSearching ) {
                        $this->searchColumn($this->builder, $columnData[0], $value, $acceptedValue);
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    /**
     * @param  array  $searchableColumns
     * @return BaseQueryFilter
     */
    public function setSearchableColumns(array $searchableColumns): self
    {
        $this->searchableColumns = $searchableColumns;

        return $this;
    }


    /**
     * @param  $column
     */
    public function sortBy($column): void
    {
        // 1. We build the array that will hold multi-column supportable
        // sort data (even if we are only sorting by a single column)
        $orderDirections = request()->get('sort_dir', 'asc');
        $orderDirections = !empty($orderDirections) ? (is_array($orderDirections) ? $orderDirections : [$orderDirections]) : [];
        foreach ($orderDirections as $index => $orderDirection) {
            $orderDirections[$index] = is_bool($orderDirection) ? ($orderDirection ? 'asc' : 'desc') : (is_string($orderDirection) && !in_array($orderDirection,[
                'asc', 'desc'
            ]) ? ($orderDirection === 'true' ? 'desc' : 'asc') : $orderDirection);
        }

        // 2. We build the array that will hold the columns by which
        // we are sorting (even if we are only sorting by a single one)
        $orderColumns = is_array($column) ? $column : [$column];

        // 3. We simply store this in memory, so we don't have to
        // request it every single time we do a left join
        $thisTableName = $this->builder->getQuery()->from;

        // 4. We store which tables we have joined, so we don't join it again
        // (in case we are sorting by multiple columns of a foreign table)
        $joinedTables = [];

        // 5. We loop through each column (or just the one)
        // e.x our user is sorting by user.first_name
        foreach ($orderColumns as $index => $orderColumn) {
            if ( !str_contains($orderColumn, '_id') && str_contains($orderColumn, 'translation_') ) {
                $localeCode = explode('_', $orderColumn)[1];
                /** @var LocaleService $localeService */
                $localeService = app(LocaleService::class);
                /** @var Locale $locale */
                $locale = $localeService->getRepositoryService()->getByCode2($localeCode);
                if ( $locale ) {
                    $this->builder->leftJoin('translations', function(JoinClause $joinClause) use ( $thisTableName, $locale ) {
                        $joinClause->on('translations.translation_key_id', '=' , $thisTableName . '.translation_key_id')
                            ->where('translations.locale_id', '=' , $locale->getAttribute(Locale::ID_COLUMN));
                    })->orderBy('translations.value', $orderDirections[$index]);
                }
                continue;
            }


            // 5.1 We loop through each override that might exist for that column
            $foundOverride = false;
            foreach ($this->sortOverrides as $key => $sortOverride) {
                // 5.2 We found the override for this column
                if ($key == $orderColumn) {
                    // 5.3 We handle the sort override in a recursive manner because the override
                    // might contain another override (a table that is joined onto that table)
                    $this->handleSortByOverride($sortOverride, $joinedTables, $thisTableName, $orderDirections[$index]);
                    $foundOverride = true;
                    break;
                }
            }
            // If we found no override we sort normally
            if (!$foundOverride) {
                $this->builder->orderBy($orderColumn, $orderDirections[$index]);
            }
        }

        // 6.0 If tables were joined we have to make sure we select
        // all data from this table, so our resources don't mess up
        if (count($joinedTables) > 0) {
            $this->builder->select($thisTableName.'.*');
        }
    }

    /**
     * @param $sortOverride
     * @param $joinedTables
     * @param $thisTableName
     * @param $orderDirection
     */
    private function handleSortByOverride($sortOverride, &$joinedTables, $thisTableName, $orderDirection)
    {
        /**
         * The example below contains a first key which says:
         * I want you to join this table by the 'key' to the
         * table and column that is the key of it's 'value'
         *
         * meaning: join thisTable.pharmacy_id = pharmacies.id
         * (thisTable being pharmacies in this example)
         *
         * The value of that array will either be another override
         * or a string, in which case it means we want to order by that
         *
         * 'pharmacy.customer_id' => [
         * 'pharmacy_id' => [
         * 'pharmacies.id' => 'customer_id'
         * ]
         * ],
         */
        if (array_key_first($sortOverride)) {
            $relationJoin = array_key_first($sortOverride);
            $relationData = $sortOverride[$relationJoin];
            $joinOn = array_key_first($relationData);
            $relationTableData = explode('.', $joinOn);
            $relationTable = $relationTableData[0];
            if (!in_array($relationTable, $joinedTables)) {
                $this->builder->leftJoin($relationTable, $joinOn, '=', $thisTableName.'.'.$relationJoin);
                $joinedTables[] = $relationTable;
            }
            $columns = array_values($relationData)[0];
            $columns = is_array($columns) ? $columns : [$columns];
            foreach ($columns as $key => $relationColumn) {
                if (is_array($relationColumn)) {
                    $this->handleSortByOverride([
                        $key => $relationColumn
                    ], $joinedTables, $relationTable, $orderDirection);
                } else {
                    $this->builder->orderBy($relationTable.'.'.$relationColumn, $orderDirection);
                }
            }
        } else {
            /*
             * The example below contains no first key
             * it's simply an override for the value address
             * so if the user sent an order_by[]=address we will
             * simply order by the overridden columns instead of
             * the potentially non-existent address column
             *
             *      'address' => [
                        'address_post_code',
                        'address_city',
                        'address_area',
                        'address_number'
                    ],
             */
            foreach ($sortOverride as $sortOverrideColumn) {
                $this->builder->orderBy($sortOverrideColumn, $orderDirection);
            }
        }
    }
}

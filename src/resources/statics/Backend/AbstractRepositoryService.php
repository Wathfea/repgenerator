<?php

namespace App\Abstraction\Repository;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class AbstractRepository.
 */
abstract class AbstractRepositoryService implements RepositoryServiceInterface
{
    private bool $cacheFilteredRequests = false;
    private bool $hasCachedFilteredRequests = false;
    private array $invalidateCacheGroupsWhenModified = [];

    /**
     * @return array
     */
    public function getInvalidateCacheGroupsWhenModified(): array
    {
        return $this->invalidateCacheGroupsWhenModified;
    }

    /**
     * @param RepositoryServiceInterface[] $invalidateCacheGroupsWhenModified
     * @return AbstractRepositoryService
     */
    public function setInvalidateCacheGroupsWhenModified(array $invalidateCacheGroupsWhenModified): AbstractRepositoryService
    {
        $this->invalidateCacheGroupsWhenModified = $invalidateCacheGroupsWhenModified;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCacheFilteredRequests(): bool
    {
        return $this->cacheFilteredRequests;
    }

    /**
     * @param bool $cacheFilteredRequests
     * @return AbstractRepositoryService
     */
    public function setCacheFilteredRequests(bool $cacheFilteredRequests): AbstractRepositoryService
    {
        $this->cacheFilteredRequests = $cacheFilteredRequests;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHasCachedFilteredRequests(): bool
    {
        return $this->hasCachedFilteredRequests;
    }

    /**
     * @param bool $hasCachedFilteredRequests
     * @return AbstractRepositoryService
     */
    public function setHasCachedFilteredRequests(bool $hasCachedFilteredRequests): AbstractRepositoryService
    {
        $this->hasCachedFilteredRequests = $hasCachedFilteredRequests;
        return $this;
    }


    /**
     * AbstractRepository constructor.
     * @param  string  $model
     */
    public function __construct(protected string $model)
    {
    }

    public function invalidateCacheGroup(): bool
    {
        return true;
    }


    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return new $this->model;
    }

    /**
     * @param  array  $load
     * @return Builder
     */
    protected function getBaseBuilder(array $load = []): Builder
    {
        $qb = $this->getModel()::query();
        if ($load) {
            $qb->with($load);
        }
        return $qb;
    }

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @return array|Collection
     */
    public function getAllByColumn(string $column, mixed $value, array $load = []): array|Collection
    {
        return $this->getByColumn($column, $value, $load)->get();
    }

    /**
     * @param  array  $columns
     * @param  array  $load
     * @return Builder|Collection
     */
    public function getAllByColumns(array $columns, array $load = []): Collection|Builder
    {
        return $this->getByColumns($columns, $load)->get();
    }

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @return Model|null
     */
    public function getFirstByColumn(string $column, mixed $value, array $load = []): Model|null
    {
        return $this->getByColumn($column, $value, $load)->first();
    }

    /**
     * @param  array  $columns
     * @param  array  $load
     * @return Model|null
     */
    public function getFirstByColumns(array $columns, array $load = []): Model|null
    {
        return $this->getByColumns($columns, $load)->first();
    }

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @return Builder
     */
    private function getByColumn(string $column, mixed $value, array $load = []): Builder
    {
        return $this->findByColumn($this->getBaseBuilder($load), $column, $value);
    }

    /**
     * @param  array  $columns
     * @param  array  $load
     * @return Builder
     */
    private function getByColumns(array $columns, array $load = []): Builder
    {
        $qb = $this->getBaseBuilder($load);
        foreach ($columns as $column => $value) {
            $qb = $this->findByColumn($qb, $column, $value);
        }

        return $qb;
    }

    /**
     * @param  Builder  $qb
     * @param  mixed  $column
     * @param  mixed  $value
     * @return Builder
     */
    private function findByColumn(Builder $qb, mixed $column, mixed $value): Builder
    {
        if (is_array($value)) {
            return $qb->whereHas($column, function (Builder $relationBuilder) use ($value) {
                $relationBuilder->where(function (Builder $qb) use ($value) {
                    foreach ($value as $column => $v) {
                        if (!is_array($v) ) {
                            if(str_contains($v, '|')) {
                                $whereValues = explode('|', $v);
                                $qb->whereIn($column, $whereValues);
                            } else {
                                $qb->where($column, $v);
                            }
                        } else {
                            $this->findByColumn($qb, $column, $v);
                        }
                    }
                });
            });
        }  else if(str_contains($value, '|')) {
            $whereValues = explode('|', $value);
            return $qb->whereIn($column, $whereValues);
        } else {
            return $qb->where($column, $value);
        }
    }

    /**
     * @param BaseQueryFilter $filter
     * @param array $load
     * @return mixed
     */
    public function getBaseFilterQB(BaseQueryFilter $filter, array $load = []): mixed
    {
        $qb = $this->getBaseBuilder($load);
        return $qb->filter($filter);
    }

    /**
     * @param Builder $builder
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    private function calculateFilterResponse(Builder $builder, int|null $perPage = null): Collection|LengthAwarePaginator {
        if ( $perPage ) {
            return $builder->paginate($perPage);
        }
        return $builder->get();
    }

    /**
     * @param Builder $builder
     * @param int|null $perPage
     * @param array $load
     * @return Collection|LengthAwarePaginator
     */
    public function getFilterResponse(Builder $builder, int|null $perPage = null, array $load = []): Collection|LengthAwarePaginator {
        // TODO: Consider adding caching layer here if as well
        return $this->calculateFilterResponse($builder, $perPage);
    }


    /**
     * @param  array  $load
     * @param  int|null  $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAll(array $load = [], int|null $perPage = null): Collection|LengthAwarePaginator
    {
        $qb = $this->getBaseBuilder($load);
        if ($perPage) {
            return $qb->paginate($perPage);
        }
        return $qb->get();
    }
}

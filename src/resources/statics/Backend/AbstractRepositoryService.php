<?php

namespace App\Abstraction\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AbstractRepository.
 */
abstract class AbstractRepositoryService implements RepositoryServiceInterface
{
    /**
     * AbstractRepository constructor.
     * @param  string  $model
     */
    public function __construct(protected string $model)
    {
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
     * @param  string|null  $orderBy
     * @param  string|null  $orderDir
     * @return Builder
     */
    protected function getBaseBuilder(array $load = [], string $orderBy = null, string $orderDir = null): Builder
    {
        $qb = $this->getModel()::query();

        if($orderBy && $orderDir) {
            $qb = $this->getModel()::query()->orderBy($orderBy, $orderDir);
        }

        if ($load) {
            $qb->with($load);
        }
        return $qb;
    }

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @return array|Collection
     */
    public function getAllByColumn(string $column, mixed $value, array $load = [], string $orderBy = 'id', string $orderDir = 'asc'): array|Collection
    {
        return $this->getByColumn($column, $value, $load, $orderBy, $orderDir)->get();
    }

    /**
     * @param  array  $columns
     * @param  array  $load
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @return Builder|Collection
     */
    public function getAllByColumns(array $columns, array $load = [], string $orderBy = 'id', string $orderDir = 'asc'): Collection|Builder
    {
        return $this->getByColumns($columns, $load, $orderBy, $orderDir)->get();
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
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @return Builder
     */
    private function getByColumn(string $column, mixed $value, array $load = [], string $orderBy = 'id', string $orderDir = 'asc'): Builder
    {
        return $this->findByColumn($this->getBaseBuilder($load, $orderBy, $orderDir), $column, $value);
    }

    /**
     * @param  array  $columns
     * @param  array  $load
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @return Builder
     */
    private function getByColumns(array $columns, array $load = [], string $orderBy = 'id', string $orderDir = 'asc'): Builder
    {
        $qb = $this->getBaseBuilder($load, $orderBy, $orderDir);
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
                            $qb->where($column, $v);
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
}

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
     * @param  int  $id
     * @param  array  $load
     * @return Model|null
     */
    public function getById(int $id, array $load = []): Model|null
    {
        return app($this->model)::with($load)->find($id);
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

    /**
     * @param BaseQueryFilter $filter
     * @param array $load
     * @return mixed
     */
    public function getFilterQB(BaseQueryFilter $filter, array $load = []) {
        $qb = $this->getBaseBuilder($load);
        return $qb->filter($filter);
    }

    /**
     * @param BaseQueryFilter $filter
     * @param  array  $load
     * @param  int|null  $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(
        BaseQueryFilter $filter,
        array $load = [],
        int $perPage = null
    ): Collection|LengthAwarePaginator {
        $qb = $this->getFilterQB($filter, $load);
        if ($perPage) {
            return $qb->paginate($perPage);
        }
        return $qb->get();
    }
}

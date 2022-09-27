<?php

namespace App\Abstraction\Repository;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class AbstractEloquentRepository.
 */
abstract class AbstractModelRepositoryService extends AbstractRepositoryService implements RepositoryServiceInterface, ModelRepositoryServiceInterface
{

    protected array $uniqueIdentifiers = [];

    /**
     * @param  int  $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        $model = $this->getById($id);
        if ($model) {
            $this->destroyOtherData($model);
            return $model->delete();
        }
        return false;
    }    /**
 * @param  array  $uniqueIdentifiers
 * @return RepositoryServiceInterface
 */
    public function setUniqueIdentifiers(array $uniqueIdentifiers): RepositoryServiceInterface
    {
        $this->uniqueIdentifiers = $uniqueIdentifiers;
        return $this;
    }

    /**
     * @param  Model  $model
     * @return bool
     */
    public function destroyOtherData(Model $model): bool
    {
        return false;
    }    /**
 * @param  Model  $model
 * @param  array  $data
 * @return bool
 */
    public function saveOtherData(Model $model, array $data): bool
    {
        return false;
    }



    /**
     * @return string
     */
    public function getModelName(): string
    {
        $modelClass = explode('\\', $this->model);
        return strtolower($modelClass[count($modelClass) - 1]);
    }

    /**
     * @param  array  $data
     * @param  array  $uniqueIdentifiers
     * @return Model|bool
     */
    public function save(array $data, array $uniqueIdentifiers = ['id']): Model|bool
    {
        /** @var Model $model */
        $qb = $this->getBaseBuilder();
        $modelSearched = false;
        foreach (array_merge($this->uniqueIdentifiers, $uniqueIdentifiers) as $uniqueIdentifier) {
            if (key_exists($uniqueIdentifier, $data)) {
                $qb->where($uniqueIdentifier, $data[$uniqueIdentifier]);
                $modelSearched = true;
            }
        }
        $model = null;
        if ($modelSearched) {
            $model = $qb->first();
        }
        $model = $model ?: $this->getModel();
        $model->fill($data);
        $model->save();
        if ($model->exists) {
            $this->saveOtherData($model, $data);
            return $model;
        }
        return false;
    }


    /**
     * @param  int  $id
     * @param  array  $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $model = $this->getById($id);
        if ($model) {
            $otherDataUpdated = $this->saveOtherData($model, $data);
            return $model->update($data) || $otherDataUpdated;
        }
        return false;
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
     * @param  array  $load
     * @return Builder
     */
    protected function getBaseBuilder(array $load = []): Builder
    {
        /** @var Builder $qb */
        $qb = app($this->model)::query();
        if ($load) {
            $qb->with($load);
        }
        return $qb;
    }


    /**
     * @param  array  $load
     * @param  int|null  $perPage
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll(array $load = [], int $perPage = null): Collection|LengthAwarePaginator
    {
        $qb = $this->getBaseBuilder($load);
        if ($perPage) {
            return $qb->paginate($perPage);
        }
        return $qb->get();
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
        $qb = $this->getBaseBuilder($load);
        $qb = $qb->filter($filter);
        if ($perPage) {
            return $qb->paginate($perPage);
        }
        return $qb->get();
    }


    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllByColumn(string $column, mixed $value, array $load = []): array|\Illuminate\Database\Eloquent\Collection
    {
        return $this->getByColumn($column, $value, $load)->get();
    }

    /**
     * @param  array  $columns
     * @param  array  $load
     * @return Builder|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllByColumns(array $columns, array $load = []): \Illuminate\Database\Eloquent\Collection|Builder
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
                        if (!is_array($column) ) {
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
     * @param  array  $exceptIds
     * @return Model|null
     */
    public function getRandom(array $exceptIds = []): Model|null
    {
        $qb = $this->getBaseBuilder();
        if (!empty($exceptIds)) {
            $qb->whereNotIn('id', $exceptIds);
        }
        return $qb->inRandomOrder()->first();
    }

    /**
     * @param  array  $exceptIds
     * @return Model
     */
    public function getRandomOrBuild(array $exceptIds = []): Model
    {
        $random = $this->getRandom($exceptIds);
        if (!$random) {
            return $this->factoryCreate();
        }
        return $random;
    }

    /**
     * @param  int  $count
     * @param  array  $attributes
     * @return Model|Collection
     */
    public function factoryMake(int $count = 1, array $attributes = []): Model|Collection
    {
        /** @var HasFactory $model */
        $model = $this->model;
        $factory = $model::factory();
        if ($count > 1) {
            $factory = $factory->count($count);
        }
        return $factory->make($attributes);
    }

    /**
     * @param  int  $count
     * @param  array  $attributes
     * @return Model|Collection
     */
    public function factoryCreate(int $count = 1, array $attributes = []): Model|Collection
    {
        $modelOrModels = $this->factoryMake($count, $attributes);
        if ($modelOrModels instanceof Collection) {
            foreach ($modelOrModels as $model) {
                $model->save();
            }
        } else {
            $modelOrModels->save();
        }
        return $modelOrModels;
    }
}

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

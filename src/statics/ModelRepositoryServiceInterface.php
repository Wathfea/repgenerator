<?php

namespace App\Abstraction\Repository;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface RepositoryInterface.
 */
interface ModelRepositoryServiceInterface extends RepositoryServiceInterface {

    /**
     * @param array $uniqueIdentifiers
     * @return RepositoryServiceInterface
     */
    public function setUniqueIdentifiers(array $uniqueIdentifiers): RepositoryServiceInterface;

    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @param string $model
     */
    public function __construct(string $model);

    /**
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function saveOtherData(Model $model, array $data): bool;

    /**
     * @param Model $model
     * @return bool
     */
    public function destroyOtherData(Model $model): bool;

    /**
     * @return string
     */
    public function getModelName(): string;

    /**
     * @param array $data
     * @param array $uniqueIdentifiers
     * @return Model|bool
     */
    public function save(array $data, array $uniqueIdentifiers = ['id']): Model|bool;


    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool;

    /**
     * @param int $id
     * @param array $load
     * @return Model|null
     */
    public function getById(int $id, array $load = []): Model | null;

    /**
     * @param array $load
     * @return Builder
     */
    public function getBaseBuilder(array $load = []): Builder;

    /**
     * @param array $load
     * @param int|null $perPage
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll(array $load = [], int $perPage = null): Collection | LengthAwarePaginator;

    /**
     * @param BaseQueryFilter $filter
     * @param array $load
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(BaseQueryFilter $filter, array $load = [], int $perPage = null): Collection | LengthAwarePaginator;


    /**
     * @param array $exceptIds
     * @return Model|null
     */
    public function getRandom(array $exceptIds = []): Model|null;

    /**
     * @param array $exceptIds
     * @return Model
     */
    public function getRandomOrBuild(array $exceptIds = []): Model;

    /**
     * @param int $count
     * @param array $attributes
     * @return Model|Collection
     */
    public function factoryMake(int $count = 1, array $attributes = []): Model | Collection;

    /**
     * @param int $count
     * @param array $attributes
     * @return Model|Collection
     */
    public function factoryCreate(int $count = 1, array $attributes = []): Model | Collection;
}

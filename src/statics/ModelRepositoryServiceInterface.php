<?php

namespace App\Abstraction\Repository\Service\Model;

use App\Abstraction\Filter\BaseQueryFilter;
use App\Abstraction\Repository\Service\RepositoryServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ModelRepositoryServiceInterface extends RepositoryServiceInterface
{
    /**
     * @param  array  $load
     * @return Collection
     */
    public function getAll(array $load = []): Collection;

    /**
     * @param  array  $data
     * @param  array  $load
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(array $data, array $load = []): LengthAwarePaginator|Collection;

    /**
     * @param  array  $data
     *
     * @return mixed
     */
    public function create(array $data): mixed;

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param  int  $id
     * @param  array  $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * @param  int  $id
     * @param  array  $load
     * @return mixed
     */
    public function getById(int $id, array $load = []): mixed;

    /**
     * @param  int  $id
     */
    public function exists(int $id);

    /**
     * This function is called after a model is created or updated
     * so that any relationships or things that don't simply get
     * saved using the fill or update function can be processed
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function saveOtherData(int $id, array $data): bool;

    /**
     * This function is called just before a model is destroyed
     * so that any remnants can get deleted that wouldn't have
     * triggered otherwise (i.e. db cascading or Laravel events)
     * @param int $id
     * @return bool
     */
    public function destroyOtherData(int $id): bool;
}

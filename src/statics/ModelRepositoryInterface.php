<?php

namespace App\Abstraction\Repository;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface RepositoryInterface.
 */
interface ModelRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $load
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAll(array $load = [], int $perPage = null): Collection | LengthAwarePaginator;

    /**
     * @param  array  $data
     * @return mixed
     */
    public function create(array $data): mixed;

    /**
     * @param  int  $id
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param  int  $id
     * @param  array  $data
     * @return bool Whether the update happened
     */
    public function update(int $id, array $data): bool;

    /**
     * @param  int  $id
     * @param  array  $load
     * @return mixed
     */
    public function getById(int $id, array $load = []): mixed;

    /**
     * @param  string  $uuid
     * @param  array  $load
     * @return mixed
     */
    public function getByUuid(string $uuid, array $load = []): mixed;

    /**
     * @param  array  $data
     * @param  array  $load
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(array $data, array $load = []): LengthAwarePaginator|Collection;

    /**
     * @param  int  $id
     * @return bool
     */
    public function isResourceExists(int $id): bool;
}

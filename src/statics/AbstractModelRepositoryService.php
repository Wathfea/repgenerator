<?php

namespace App\Abstraction\Repository\Service\Model;

use App\Abstraction\Filter\BaseQueryFilter;
use App\Abstraction\Repository\ModelRepositoryInterface;
use App\Abstraction\Repository\RepositoryInterface;
use App\Abstraction\Repository\Service\AbstractRepositoryService;
use App\Abstraction\Repository\Service\RepositoryServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class AbstractModelRepositoryService extends AbstractRepositoryService implements RepositoryServiceInterface, ModelRepositoryServiceInterface
{
    /**
     * @param  array  $data
     * @return mixed
     */
    public function create(array $data): mixed
    {
        return $this->getRepository()->create($data);
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->getRepository()->delete($id);
    }

    /**
     * @param  int  $id
     */
    public function exists(int $id)
    {
        abort_unless($this->repository->isResourceExists($id), 404, 'Shipping not found');
    }

    /**
     * @param  array  $load
     * @return Collection
     */
    public function getAll(array $load = []): Collection
    {
        return $this->getRepository()->getAll($load);
    }

    /**
     * @return ModelRepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * @param  array  $data
     * @param  array  $load
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(array $data, array $load = []): LengthAwarePaginator|Collection
    {
        return $this->getRepository()->getByFilter($data, $load);
    }

    /**
     * @param  int  $id
     * @param  array  $load
     * @return mixed
     */
    public function getById(int $id, array $load = []): mixed
    {
        return $this->getRepository()->getById($id, $load);
    }

    /**
     * @param  int  $id
     * @param  array  $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->getRepository()->update($id, $data);
    }

    /**
     * @param  string  $uuid
     * @param  array  $load
     * @return mixed
     */
    public function getByUuid(string $uuid, array $load = []): mixed
    {
        return $this->getRepository()->getByUuid($uuid, $load);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function saveOtherData(int $id, array $data): bool
    {
        return false;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function destroyOtherData(int $id): bool
    {
        return false;
    }
}

<?php

namespace App\Abstraction\Repository\Service\Pivot;

use App\Abstraction\Repository\PivotRepositoryInterface;
use App\Abstraction\Repository\RepositoryInterface;
use App\Abstraction\Repository\Service\AbstractRepositoryService;
use App\Abstraction\Repository\Service\RepositoryServiceInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

abstract class AbstractPivotRepositoryService extends AbstractRepositoryService implements RepositoryServiceInterface, PivotRepositoryServiceInterface
{
    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @param  array  $data
     * @return bool
     */
    public function attach(int $parentModelId, int $childModelId, array $data = []): bool
    {
        return $this->getRepository()->attach($parentModelId, $childModelId, $data);
    }

    /**
     * @return PivotRepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @return bool
     */
    public function detach(int $parentModelId, int $childModelId): bool
    {
        return $this->getRepository()->detach($parentModelId, $childModelId);
    }

    /**
     * @param  int  $id
     */
    public function exists(int $id)
    {
        abort_unless($this->repository->isResourceExists($id), 404, 'Shipping not found');
    }

    /**
     * @param  int  $parentModelId
     * @return Collection
     */
    public function getById(int $parentModelId): Collection
    {
        return $this->getRepository()->get($parentModelId);
    }

    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @return Pivot|null
     */
    public function getSpecific(int $parentModelId, int $childModelId): Pivot|null
    {
        return $this->getRepository()->getSpecific($parentModelId, $childModelId);
    }

    /**
     * @param  int  $parentModelId
     * @param  array  $relations
     * @return bool
     */
    public function sync(int $parentModelId, array $relations): bool
    {
        return $this->getRepository()->sync($parentModelId, $relations);
    }

    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @param  array  $data
     * @return bool
     */
    public function update(int $parentModelId, int $childModelId, array $data = []): bool
    {
        return $this->getRepository()->update($parentModelId, $childModelId, $data);
    }
}

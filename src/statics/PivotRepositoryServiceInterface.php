<?php

namespace App\Abstraction\Repository\Service\Pivot;

use App\Abstraction\Repository\Service\RepositoryServiceInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

interface PivotRepositoryServiceInterface extends RepositoryServiceInterface
{
    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @param  array  $data
     * @return bool
     */
    public function attach(int $parentModelId, int $childModelId, array $data = []): bool;

    /**
     * @param  int  $parentModelId
     * @param  array  $relations
     * @return bool
     */
    public function sync(int $parentModelId, array $relations): bool;


    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @param  array  $data
     * @return bool
     */
    public function update(int $parentModelId, int $childModelId, array $data = []): bool;

    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @return bool
     */
    public function detach(int $parentModelId, int $childModelId): bool;

    /**
     * @param  int  $parentModelId
     * @return Collection
     */
    public function getById(int $parentModelId): Collection;

    /**
     * @param  int  $parentModelId
     * @param  int  $childModelId
     * @return Pivot|null
     */
    public function getSpecific(int $parentModelId, int $childModelId): Pivot|null;

    /**
     * @param  int  $id
     */
    public function exists(int $id);
}

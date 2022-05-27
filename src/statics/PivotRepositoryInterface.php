<?php

namespace App\Abstraction\Repository;

use Illuminate\Support\Collection;

/**
 * Interface PivotRepositoryInterface.
 */
interface PivotRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  int  $parentModelId
     * @return Collection
     */
    public function get(int $parentModelId): Collection;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @return mixed
     */
    public function getSpecific(int $parentModelId, int $relationModelId): mixed;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return bool
     */
    public function update(int $parentModelId, int $relationModelId, array $data = []): bool;

    /**
     * @param  int  $parentModelId
     * @param  array  $relations
     * @return bool
     */
    public function sync(int $parentModelId, array $relations): bool;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationshipModelId
     * @param  array  $data
     * @return bool
     */
    public function attach(int $parentModelId, int $relationshipModelId, array $data = []): bool;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationshipModelId
     * @return bool
     */
    public function detach(int $parentModelId, int $relationshipModelId): bool;
}

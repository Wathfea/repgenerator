<?php

namespace App\Abstraction\Repository;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface PivotRepositoryInterface.
 */
interface PivotRepositoryServiceInterface extends RepositoryServiceInterface
{
    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return Pivot
     */
    public function attach(int $parentModelId, int $relationModelId, array $data = []): Pivot;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return Pivot|bool
     */
    public function attachOrUpdate(int $parentModelId, int $relationModelId, array $data = []): Pivot|bool;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @return bool
     */
    public function detach(int $parentModelId, int $relationModelId): bool;

    /**
     * @param  int  $parentModelId
     * @return Collection
     */
    public function get(int $parentModelId): Collection;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @return Pivot|null
     */
    public function getSpecific(int $parentModelId, int $relationModelId): Pivot|null;

    /**
     * @param  int  $parentModelId
     * @param  array  $relations
     * @return bool
     */
    public function sync(int $parentModelId, array $relations): bool;

    /**
     * @param int $parentModelId
     * @param array $relations
     * @param array $data
     * @return bool
     */
    public function syncWithData(int $parentModelId, array $relations, array $data): bool;

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return bool
     */
    public function updateData(int $parentModelId, int $relationModelId, array $data = []): bool;

    /**
     * @return string
     */
    public function getParentColumnName(): string;

    /**
     * @return string
     */
    public function getRelationColumnName(): string;


    /**
     * @param int $parentModelId
     * @param int $relationModelId
     * @param array $data
     * @return bool
     */
    public function beforeSaving(int $parentModelId, int $relationModelId, array $data = []): bool;


    /**
     * @param BaseQueryFilter $filter
     * @param int $parentId
     * @param array $load
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(
        BaseQueryFilter $filter,
        int $parentId,
        array $load = [],
        int|null $perPage = null
    ): Collection|LengthAwarePaginator;
}

<?php

namespace App\Abstraction\Repository;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

/**
 * Class AbstractEloquentPivotModelRepository.
 */
abstract class AbstractPivotRepositoryService extends AbstractRepositoryService implements RepositoryServiceInterface, PivotRepositoryServiceInterface
{
    /**
     * AbstractEloquentRepository constructor.
     * @param  string  $pivotModel
     * @param  string  $parentModel
     * @param  string  $parentIdColumName
     * @param  string  $relationIdColumnName
     * @param  string  $relatedTableName
     */
    #[Pure]
    public function __construct(
        private string $pivotModel,
        private string $parentModel,
        private string $parentIdColumName,
        private string $relationIdColumnName,
        private string $relatedTableName
    ) {
        parent::__construct($pivotModel);
    }

    public function getParentColumnName(): string {
        return Str::lower(Str::snake($this->getParentModelName())) . '_' . $this->parentIdColumName;
    }

    public function getRelationColumnName(): string {
        return Str::singular($this->relatedTableName) . '_' . $this->relationIdColumnName;
    }

    /**
     * @param string $modelName
     * @return string
     */
    private function getNameFromClass(string $modelName): string {
        $modelClass = explode('\\', $modelName);
        return $modelClass[count($modelClass) - 1];
    }

    /**
     * @return string
     */
    public function getParentModelName(): string
    {
        return $this->getNameFromClass($this->parentModel);
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->getNameFromClass($this->pivotModel);
    }

    /**
     * @param array $data
     * @return array
     */
    private function filterData(array $data): array {
        return array_filter($data, function($datum, $columnName){
            /** @var Pivot $pivotClass */
            $pivotClass = app($this->pivotModel);
            $fillable = $pivotClass->getFillable();
            return in_array($columnName, $fillable);
        }, ARRAY_FILTER_USE_BOTH);
    }


    /**
     * @param int $parentModelId
     * @param int $relationModelId
     * @param array $data
     * @return bool
     */
    public function beforeSaving(int $parentModelId, int $relationModelId, array $data = []): bool
    {
        return false;
    }


    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return Pivot
     */
    public function attach(int $parentModelId, int $relationModelId, array $data = []): Pivot
    {
        $this->beforeSaving($parentModelId, $relationModelId,  $data);
        $this->getRelation($parentModelId)->attach($relationModelId, $this->filterData($data));

        return $this->getSpecific($parentModelId, $relationModelId);
    }

    /**
     * @param  int  $parentModelId
     * @return BelongsToMany
     */
    private function getRelation(int $parentModelId): BelongsToMany
    {
        /** @var Model $model */
        $model = app($this->parentModel)->newQuery()->find($parentModelId);
        $relation = Str::camel($this->relatedTableName);

        return $model->$relation();
    }

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @return bool
     */
    public function detach(int $parentModelId, int $relationModelId): bool
    {
        return $this->getRelation($parentModelId)->detach($relationModelId) > 0;
    }

    /**
     * @param  int  $parentModelId
     * @return Collection
     */
    public function get(int $parentModelId): Collection
    {
        return app($this->pivotModel)->newQuery()->where($this->parentIdColumName, $parentModelId)->get();
    }

    /**
     * @param int $parentModelId
     * @param int $relationModelId
     * @param array $load
     * @return Pivot|null
     */
    public function getSpecific(int $parentModelId, int $relationModelId, array $load = []): Pivot|null
    {
        return app($this->pivotModel)->newQuery()
            ->where($this->getParentColumnName(), $parentModelId)
            ->where($this->getRelationColumnName(), $relationModelId)
            ->with($load)
            ->first();
    }

    /**
     * @param  int  $parentModelId
     * @param  array  $relations
     * @return bool
     */
    public function sync(int $parentModelId, array $relations): bool
    {
        $this->getRelation($parentModelId)->sync($relations);

        return true;
    }


    /**
     * @param int $parentModelId
     * @param array $relations
     * @param array $data
     * @return bool
     */
    public function syncWithData(int $parentModelId, array $relations, array $data): bool
    {
        $this->getRelation($parentModelId)->syncWithPivotValues($relations, $data);

        return true;
    }


    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return bool
     */
    public function updateData(int $parentModelId, int $relationModelId, array $data = []): bool
    {
        $data = $this->filterData($data);
        if ( !empty($data) ) {
            $this->beforeSaving($parentModelId, $relationModelId,  $data);
            return $this->getRelation($parentModelId)->updateExistingPivot($relationModelId, $data) > 0;
        }
        return true;
    }

    /**
     * @param BaseQueryFilter $filter
     * @param int $parentId
     * @param array $load
     * @return mixed
     */
    public function getFilterQB(BaseQueryFilter $filter, int $parentId, array $load = []): mixed
    {
        $qb = $this->getBaseFilterQB($filter, $load);
        return $qb->where($this->getParentColumnName(), $parentId);
    }

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
    ): Collection|LengthAwarePaginator {
        $qb = $this->getFilterQB($filter, $parentId, $load);
        return $this->getFilterResponse($qb, $perPage);
    }
}

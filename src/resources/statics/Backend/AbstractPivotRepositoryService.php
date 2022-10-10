<?php

namespace App\Abstraction\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
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

    /**
     * @param  int  $parentModelId
     * @param  int  $relationshipModelId
     * @param  array  $data
     * @return bool
     */
    public function attach(int $parentModelId, int $relationshipModelId, array $data = []): bool
    {
        $this->getRelation($parentModelId)->attach($relationshipModelId, $data);

        return true;
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
     * @param  int  $relationshipModelId
     * @return bool
     */
    public function detach(int $parentModelId, int $relationshipModelId): bool
    {
        return $this->getRelation($parentModelId)->detach($relationshipModelId) > 0;
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
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @return Pivot|null
     */
    public function getSpecific(int $parentModelId, int $relationModelId): Pivot|null
    {
        return app($this->pivotModel)->newQuery()->where($this->parentIdColumName, $parentModelId)
            ->where($this->relationIdColumnName, $relationModelId)
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
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @param  array  $data
     * @return bool
     */
    public function updateData(int $parentModelId, int $relationModelId, array $data = []): bool
    {
        return $this->getRelation($parentModelId)->updateExistingPivot($relationModelId, $data) > 0;
    }
}

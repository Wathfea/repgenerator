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
     * @param  Pivot  $pivot
     * @param  Model  $parent
     * @param  string  $parentIdColumName
     * @param  string  $relationIdColumnName
     * @param  string  $relation
     */
    #[Pure]
    public function __construct(
        private Pivot $pivot,
        private Model $parent,
        private string $parentIdColumName,
        private string $relationIdColumnName,
        private string $relation
    ) {
        parent::__construct($pivot);
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
        $model = $this->parent->newQuery()->find($parentModelId);
        $relation = Str::camel($this->relation);

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
        return $this->pivot->newQuery()->where($this->parentIdColumName, $parentModelId)->get();
    }

    /**
     * @param  int  $parentModelId
     * @param  int  $relationModelId
     * @return Model|null
     */
    public function getSpecific(int $parentModelId, int $relationModelId): Model|null
    {
        return $this->pivot->newQuery()->where($this->parentIdColumName, $parentModelId)
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
    public function update(int $parentModelId, int $relationModelId, array $data = []): bool
    {
        return $this->getRelation($parentModelId)->updateExistingPivot($relationModelId, $data) > 0;
    }
}

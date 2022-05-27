<?php

namespace App\Abstraction\Repository\Eloquent\Model;

use App\Abstraction\Filter\BaseQueryFilter;
use App\Abstraction\Repository\Eloquent\AbstractEloquentRepository;
use App\Abstraction\Repository\ModelRepositoryInterface;
use App\Abstraction\Repository\RepositoryInterface;
use App\Domain\Abstract\CRUD\Services\AbstractCRUDService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class AbstractEloquentRepository.
 */
abstract class AbstractEloquentModelRepository extends AbstractEloquentRepository implements RepositoryInterface, ModelRepositoryInterface, EloquentModelRepositoryInterface
{

    protected array $uniqueIdentifiers = [];

    /**
     * @param array $uniqueIdentifiers
     * @return AbstractEloquentRepository
     */
    public function setUniqueIdentifiers(array $uniqueIdentifiers): AbstractEloquentRepository
    {
        $this->uniqueIdentifiers = $uniqueIdentifiers;
        return $this;
    }

    /**
     * @var string
     */
    private string $filterClass;

    /**
     * @return string
     */
    public function getFilterClass(): string
    {
        return $this->filterClass;
    }

    /**
     * @param  string  $filterClass
     * @return AbstractEloquentModelRepository
     */
    public function setFilterClass(string $filterClass): self
    {
        $this->filterClass = $filterClass;

        return $this;
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

    /**
     * @param array $data
     * @param array $uniqueIdentifiers
     * @return Model|bool
     */
    public function create(array $data, array $uniqueIdentifiers = ['id']): Model|bool
    {
        /** @var Model $model */
        $qb = $this->getQB();
        $modelSearched = false;
        foreach (array_merge($this->uniqueIdentifiers, $uniqueIdentifiers) as $uniqueIdentifier) {
            if (key_exists($uniqueIdentifier, $data)) {
                $qb->where($uniqueIdentifier, $data[$uniqueIdentifier]);
                $modelSearched = true;
            }
        }
        $model = null;
        if ($modelSearched) {
            $model = $qb->first();
        }
        $model = $model ?: new $this->model;
        $model->fill($data);
        $model->save();
        if ($model->exists) {
            $this->saveOtherData($model->id, $data);
            return $model;
        }
        return false;
    }

    /**
     * @param  array  $load
     * @return Builder
     */
    public function getQB(array $load = []): Builder
    {
        $qb = $this->model->newQuery();
        if (!empty($load)) {
            $qb->with($load);
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        $model = $this->getById($id);
        if ( $model ) {
            $this->destroyOtherData($model);
            return $model->delete();
        }
        return false;
    }

    /**
     * @param array $load
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAll(array $load = [], int $perPage = null): Collection | LengthAwarePaginator {
        $qb = $this->getQB($load);
        if ( $perPage ) {
            return $qb->paginate($perPage);
        }
        return $qb->get();
    }

    /**
     * @param  array  $data
     * @param  array  $load
     * @return Collection|LengthAwarePaginator
     */
    public function getByFilter(array $data, array $load = []): LengthAwarePaginator|Collection
    {
        $filtered = $this->getQB($load)->filter($this->hasFilterClass() ? app($this->filterClass,
            $data) : new BaseQueryFilter($data));

        if (array_key_exists('per_page', $data)) {
            return $filtered->paginate($data['per_page']);
        } else {
            return $filtered->get();
        }
    }


    /**
     * @param  int  $id
     * @return bool
     */
    public function isResourceExists(int $id): bool
    {
        return $this->getQB()->where('id', $id)->exists();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $model = $this->getById($id);
        if ( $model ) {
            $otherDataUpdated = $this->saveOtherData($model, $data);
            return $model->update($data) || $otherDataUpdated;
        }
        return false;
    }

    /**
     * @param  int  $id
     * @param  array  $load
     * @return mixed
     */
    public function getById(int $id, array $load = []): mixed
    {
        return $this->getQB($load)->find($id);
    }

    /**
     * @param  string  $uuid
     * @param  array  $load
     * @return mixed
     */
    public function getByUuid(string $uuid, array $load = []): mixed
    {
        return $this->getQB($load)->find($uuid);
    }

    /**
     * @return bool
     */
    public function hasFilterClass(): bool
    {
        return !empty($this->filterClass);
    }


}

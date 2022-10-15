<?php

namespace App\Abstraction\Controllers;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractApiReadOnlyCRUDController extends AbstractCRUDController implements CRUDControllerInterface, ApiCRUDControllerReadOnlyInterface
{
    /**
     * @var string
     */
    private string $resourceClass = JsonResource::class;

    /**
     * @var string
     */
    private string $filterClass = BaseQueryFilter::class;

    /**
     * @var int
     */
    private int $perPage = 10;

    /**
     * @var array
     */
    private array $relations = [];

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param  array  $relations
     */
    public function setRelations(array $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * @param  array  $newRelations
     * @return void
     */
    public function addToRelations(array $newRelations): void
    {
        $this->setRelations(array_merge($this->getRelations(), $newRelations));
    }

    /**
     * @param  array  $removableRelations
     * @return void
     */
    public function removeFromRelatons(array $removableRelations): void
    {
        $this->setRelations(array_diff($this->getRelations(), $removableRelations));
    }

    /**
     * @param  Request  $request
     * @param  array  $relationships
     * @return JsonResponse
     */
    public function index(Request $request, array $relationships = []): JsonResponse
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        $filter = app($this->getFilterClass(), $request->all());
        $perPage = $this->getPerPage($request);
        $this->addToRelations($relationships);
        return $resource::collection($this->getService()->getRepositoryService()->getByFilter($filter, $this->getRelations(),
            $perPage))->toResponse($request);
    }

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    /**
     * @param  string  $resourceClass
     * @return AbstractCrudController
     */
    public function setResourceClass(string $resourceClass): AbstractCrudController
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterClass(): string
    {
        return $this->filterClass;
    }

    /**
     * @param  string  $filterClass
     * @return AbstractCrudController
     */
    public function setFilterClass(string $filterClass): AbstractCrudController
    {
        $this->filterClass = $filterClass;
        return $this;
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    protected function getPerPage(Request $request): mixed
    {
        if(!$request->has('per_page')) {
            return null;
        }

        $perPage = $request->get('per_page');
        if ($perPage > 0) {
            return $perPage;
        }
        return $this->perPage;
    }

    /**
     * @param  int  $perPage
     * @return AbstractCrudController
     */
    public function setPerPage(int $perPage): AbstractCrudController
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @param  array  $relationships
     * @return JsonResponse
     */
    public function show(Request $request, int $id, array $relationships = []): JsonResponse
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        $this->addToRelations($relationships);
        return $resource::make($this->getService()->getRepositoryService()->getById($id, $this->getRelations()))->toResponse($request);
    }
}

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
    private string $filterClass = BaseQueryFilter::class;
    private int $perPage = 10;

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    /**
     * @param string $resourceClass
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
     * @param string $filterClass
     * @return AbstractCrudController
     */
    public function setFilterClass(string $filterClass): AbstractCrudController
    {
        $this->filterClass = $filterClass;
        return $this;
    }

    /**
     * @param int $perPage
     * @return AbstractCrudController
     */
    public function setPerPage(int $perPage): AbstractCrudController
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getPerPage(Request $request): mixed {
        $perPage = $request->get('per_page');
        if ( $perPage > 0 ) {
            return $perPage;
        }
        return $this->perPage;
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
        $filter = app($this->getFilterClass(),$request->all());
        $perPage = $this->getPerPage($request);
        return $resource::collection($this->getService()->getRepositoryService()->getByFilter($filter, $relationships, $perPage))->toResponse($request);
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        return $resource::make($this->getService()->getRepositoryService()->getById($id))->toResponse($request);
    }
}

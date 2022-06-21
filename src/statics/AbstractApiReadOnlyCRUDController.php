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

    public function index(Request $request): JsonResponse
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        $filter = app($this->getFilterClass(),$request->all());
        $perPage = $this->getPerPage($request);
        return response()->json($resource::collection($this->getService()->getRepositoryService()->getByFilter($filter, [],$perPage)));
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        return response()->json($resource::make($this->getService()->getRepositoryService()->getById($id)));
    }
}

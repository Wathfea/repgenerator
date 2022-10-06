<?php

namespace App\Abstraction\Controllers;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class AbstractApiReadOnlyCRUDController extends AbstractCRUDController implements CRUDControllerInterface, ApiCRUDControllerReadOnlyInterface
{
    /**
     * @param Request $request
     * @return BaseQueryFilter
     */
    public function getFilter(Request $request): BaseQueryFilter {
        return app($this->getFilterClass(), $request->all());
    }

    /**
     * @var string
     */
    private string $resourceClass = JsonResource::class;
    private string $filterClass = BaseQueryFilter::class;
    private int $perPage = 10;

    public function getIndexData(Request $request, array $relationships = []): AnonymousResourceCollection
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        $filter = $this->getFilter($request);
        $perPage = $this->getPerPage($request);
        if ( $request->has('load') && !empty($request->get('load')) ) {
            $relationships = array_intersect($relationships, $request->get('load'));
        }
        return $resource::collection($this->getService()->getRepositoryService()->getByFilter($filter, $relationships,
            $perPage));
    }

    /**
     * @param  Request  $request
     * @param  array  $relationships
     * @return JsonResponse
     */
    public function index(Request $request, array $relationships = []): JsonResponse
    {
        return $this->getIndexData($request, $relationships)->toResponse($request);
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
        return $resource::make($this->getService()->getRepositoryService()->getById($id, $relationships))->toResponse($request);
    }
}

<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Controllers\AbstractController;
use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadOnlyControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractApiReadOnlyCRUDController extends AbstractController implements ControllerInterface, ReadOnlyControllerInterface, CRUDControllerInterface, ApiReadOnlyCRUDControllerInterface
{
    /** @var string  */
    protected string $showRequest = '';

    /**
     * @param string $showRequest
     * @return AbstractApiReadOnlyCRUDController
     */
    public function setShowRequest(string $showRequest): AbstractApiReadOnlyCRUDController
    {
        $this->showRequest = $showRequest;
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
        if ( !empty($this->showRequest) ) {
            $request->validate(app($this->showRequest)->rules());
        }
        $this->addToRelations($relationships);
        $model = $this->getService()->getRepositoryService()->getById($id, $this->getRelations());
        return $this->getShowResponse($request, $model);
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getIndexData(Request $request): AnonymousResourceCollection
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        $filter = $this->getFilter($request);
        $perPage = $this->getPerPage($request);
        $data = $this->getService()->getRepositoryService()->getByFilter($filter, $this->getLoad($request), $perPage);
        return $resource::collection($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->getListResponse($request, function() use ($request) {
           return $this->getIndexData($request);
        });
    }
}

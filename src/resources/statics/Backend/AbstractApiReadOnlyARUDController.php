<?php

namespace App\Abstraction\Controllers\ARUD;

use App\Abstraction\Controllers\AbstractController;
use App\Abstraction\Controllers\ControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractApiReadOnlyARUDController extends AbstractController implements ControllerInterface, ARUDControllerInterface, ApiReadOnlyARUDControllerInterface
{
    /**
     * @param Request $request
     * @param int $parentId
     * @param int $relationId
     * @param array $relationships
     * @return JsonResponse
     */
    public function show(Request $request, int $parentId, int $relationId, array $relationships = []): JsonResponse
    {
        $this->addToRelations($relationships);
        $pivot = $this->getService()->getRepositoryService()->getSpecific($parentId, $relationId);
        return $this->getShowResponse($request, $pivot);
    }

    /**
     * @param Request $request
     * @param int $parentId
     * @return AnonymousResourceCollection
     */
    public function getIndexData(Request $request, int $parentId): AnonymousResourceCollection
    {
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        $filter = $this->getFilter($request);
        $perPage = $this->getPerPage($request);
        $data = $this->getService()->getRepositoryService()->getByFilter($filter, $parentId, $this->getLoad($request), $perPage);
        return $resource::collection($data);
    }

    /**
     * @param Request $request
     * @param int $parentId
     * @return JsonResponse
     */
    public function index(Request $request, int $parentId): JsonResponse
    {
        return $this->getListResponse($request, function() use ($request, $parentId) {
            return $this->getIndexData($request, $parentId);
        });
    }
}

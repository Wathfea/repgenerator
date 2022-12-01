<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Cache\CacheGroupService;
use App\Abstraction\Controllers\AbstractController;
use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadOnlyControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

abstract class AbstractApiReadOnlyCRUDController extends AbstractController implements ControllerInterface, ReadOnlyControllerInterface, CRUDControllerInterface, ApiReadOnlyCRUDControllerInterface
{
    /** @var string  */
    protected string $showRequest = '';

    /** @var bool  */
    private bool $cacheFilteredRequests = false;

    /**
     * @return bool
     */
    public function isCacheFilteredRequests(): bool
    {
        return $this->cacheFilteredRequests;
    }

    /**
     * @param bool $cacheFilteredRequests
     * @return AbstractApiReadOnlyCRUDController
     */
    public function setCacheFilteredRequests(bool $cacheFilteredRequests): AbstractApiReadOnlyCRUDController
    {
        $this->cacheFilteredRequests = $cacheFilteredRequests;
        return $this;
    }

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
        $filter = $this->getFilter($request->all());
        $perPage = $this->getPerPage($request);
        $data = $this->getService()->getRepositoryService()->getByFilter($filter, $this->getLoad($request), $perPage);
        return $resource::collection($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    private function calculateListResponse(Request $request) {
        return $this->getListResponse($request, function() use ($request) {
            return $this->getIndexData($request);
        });
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if ( $this->isCacheFilteredRequests() ) {
            $cacheKey = md5(serialize([
                'filters' => $request->all(),
                'load' => $this->getLoad($request)
            ]));
            if ( Cache::has($cacheKey) ) {
                return unserialize(Cache::get($cacheKey));
            } else {
                $data = $this->calculateListResponse($request);
                if ( Cache::put($cacheKey, serialize($data)) ) {
                    CacheGroupService::addCache($this->getService()->getRepositoryService()->getModelName(), $data);
                }
                return $data;
            }
        }
        return $this->calculateListResponse($request);
    }
}

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
     * @var array
     */
    private array $clientSpecificCacheDependency = [];

    /**
     * @param Request $request
     * @return array
     */
    public function getClientSpecificCacheDependency(Request $request): array
    {
        return $this->clientSpecificCacheDependency;
    }

    /**
     * @param array $clientSpecificCacheDependency
     * @return AbstractApiReadOnlyCRUDController
     */
    public function setClientSpecificCacheDependency(array $clientSpecificCacheDependency): AbstractApiReadOnlyCRUDController
    {
        $this->clientSpecificCacheDependency = $clientSpecificCacheDependency;
        return $this;
    }

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
        $this->getService()->getRepositoryService()->setCacheFilteredRequests($cacheFilteredRequests);
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
            $cacheKeyArray = array_merge([
                'filters' => $request->all(),
                'load' => $this->getLoad($request)
            ], $this->getClientSpecificCacheDependency($request));
            $cacheKey = md5(serialize($cacheKeyArray));
            $requiresSerialization = config('cache.default') != 'redis';
            if ( Cache::has($cacheKey) ) {
                $data = Cache::get($cacheKey);
                if ( $requiresSerialization ) {
                    $data = unserialize($data);
                }
                return $data;
            } else {
                $data = $this->calculateListResponse($request);
                $storeResult = $requiresSerialization ? serialize($data) : $data;
                if ( Cache::put($cacheKey, $storeResult) ) {
                    CacheGroupService::addCache(get_class($this->getService()->getRepositoryService()->getModel()), $cacheKey);
                }
                return $data;
            }
        }
        return $this->calculateListResponse($request);
    }
}

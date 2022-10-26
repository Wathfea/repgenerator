<?php

namespace App\Abstraction\Controllers\ARUD;

use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadWriteControllerInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractApiReadWriteARUDController extends AbstractApiReadOnlyARUDController implements ControllerInterface, ReadWriteControllerInterface, ARUDControllerInterface, ApiReadOnlyARUDControllerInterface, ApiReadWriteARUDControllerInterface
{
    /** @var string  */
    protected string $attachRequest = '';

    /** @var string  */
    protected string $updateRequest = '';

    /** @var string  */
    protected string $detachRequest = '';

    /**
     * @param string $attachRequest
     * @return AbstractApiReadWriteARUDController
     */
    public function setAttachRequest(string $attachRequest): AbstractApiReadWriteARUDController
    {
        $this->attachRequest = $attachRequest;
        return $this;
    }

    /**
     * @param string $updateRequest
     * @return AbstractApiReadWriteARUDController
     */
    public function setUpdateRequest(string $updateRequest): AbstractApiReadWriteARUDController
    {
        $this->updateRequest = $updateRequest;
        return $this;
    }

    /**
     * @param string $detachRequest
     * @return AbstractApiReadWriteARUDController
     */
    public function setDetachRequest(string $detachRequest): AbstractApiReadWriteARUDController
    {
        $this->detachRequest = $detachRequest;
        return $this;
    }

    /**
     * @param Request $request
     * @param Pivot $pivot
     * @param int $parentId
     * @param int $relationId
     * @return bool
     */
    public function handleDetach(Request $request, Pivot $pivot, int $parentId, int $relationId): bool {
        return $this->getService()->getRepositoryService()->detach(
            $parentId,
            $relationId
        );
    }

    /**
     * @param Request $request
     * @param int $parentId
     * @param int $relationId
     * @return JsonResponse
     */
    public function detach(Request $request, int $parentId, int $relationId): JsonResponse
    {
        return $this->getDestroyOrDetachResponse($request, function() use ($parentId, $relationId) {
            return $this->getService()->getRepositoryService()->getSpecific($parentId, $relationId);
        },function(Pivot $pivot) use ($request, $parentId, $relationId) {
            return $this->handleDetach($request, $pivot, $parentId, $relationId);
        }, $this->detachRequest);
    }

    /**
     * @param Request $request
     * @param int $parentId
     * @param int $relationId
     * @return Pivot
     */
    public function handleAttach(Request $request, int $parentId, int $relationId): Pivot
    {
        return $this->getService()->getRepositoryService()->attach(
            $parentId,
            $relationId,
            $request->all()
        );
    }

    /**
     * @param Request $request
     * @param int $parentId
     * @param int $relationId
     * @return JsonResponse
     */
    public function attach(Request $request, int $parentId, int $relationId): JsonResponse
    {
        return $this->getStoreOrAttachResponse($request, function() use ($request, $parentId, $relationId) {
            return $this->handleAttach($request, $parentId, $relationId);
        }, $this->attachRequest);
    }

    /**
     * @param Request $request
     * @param Pivot $pivot
     * @param int $parentId
     * @param int $relationId
     * @return bool
     */
    public function handleUpdate(Request $request, Pivot $pivot, int $parentId, int $relationId): bool
    {
        return $this->getService()->getRepositoryService()->updateData(
            $parentId,
            $relationId,
            $request->all()
        );
    }

    /**
     * @param Request $request
     * @param int $parentId
     * @param int $relationId
     * @return JsonResponse
     */
    public function update(Request $request, int $parentId, int $relationId): JsonResponse
    {
        return $this->getUpdateResponse($request, function() use ($parentId, $relationId) {
            return $this->getService()->getRepositoryService()->getSpecific($parentId, $relationId);
        },function(Pivot $pivot) use ($request, $parentId, $relationId) {
            return $this->handleUpdate($request, $pivot, $parentId, $relationId);
        }, $this->updateRequest);
    }
}

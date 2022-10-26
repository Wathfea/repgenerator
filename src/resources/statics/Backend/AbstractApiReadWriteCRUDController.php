<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadWriteControllerInterface;
use App\Abstraction\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractApiReadWriteCRUDController extends AbstractApiReadOnlyCRUDController implements ControllerInterface, ReadWriteControllerInterface, CRUDControllerInterface, ApiReadOnlyCRUDControllerInterface, ApiReadWriteCRUDControllerInterface
{
    /** @var string  */
    protected string $storeRequest = '';

    /** @var string  */
    protected string $updateRequest = '';

    /** @var string  */
    protected string $destroyRequest = '';

    /**
     * @param string $storeRequest
     * @return AbstractApiReadWriteCRUDController
     */
    public function setStoreRequest(string $storeRequest): AbstractApiReadWriteCRUDController
    {
        $this->storeRequest = $storeRequest;
        return $this;
    }

    /**
     * @param string $updateRequest
     * @return AbstractApiReadWriteCRUDController
     */
    public function setUpdateRequest(string $updateRequest): AbstractApiReadWriteCRUDController
    {
        $this->updateRequest = $updateRequest;
        return $this;
    }

    /**
     * @param string $destroyRequest
     * @return AbstractApiReadWriteCRUDController
     */
    public function setDestroyRequest(string $destroyRequest): AbstractApiReadWriteCRUDController
    {
        $this->destroyRequest = $destroyRequest;
        return $this;
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return bool
     */
    public function handleDestroy(Request $request, Model $model): bool
    {
        return $this->getService()->getRepositoryService()->destroy($model->getAttribute(BaseModel::ID_COLUMN));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->getDestroyOrDetachResponse($request, function() use ($id) {
            return $this->getService()->getRepositoryService()->getById($id);
        },function (Model $model) use ($request) {
            return $this->handleDestroy($request, $model);
        }, $this->destroyRequest);
    }

    /**
     * @param Request $request
     * @return Model
     */
    public function handleStore(Request $request): Model
    {
        return $this->getService()->getRepositoryService()->save($request->all());
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        return $this->getStoreOrAttachResponse($request, function() use ($request) {
            return $this->handleStore($request);
        }, $this->storeRequest);
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return bool
     */
    public function handleUpdate(Request $request, Model $model): bool
    {
        return $this->getService()->getRepositoryService()->update($model->getAttribute(BaseModel::ID_COLUMN), $request->all());
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->getUpdateResponse($request, function() use ($id) {
            return $this->getService()->getRepositoryService()->getById($id);
        },function (Model $model) use ($request) {
            return $this->handleUpdate($request, $model);
        }, $this->updateRequest);
    }
}

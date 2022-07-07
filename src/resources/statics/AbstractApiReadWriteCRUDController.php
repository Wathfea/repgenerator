<?php

namespace App\Abstraction\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

abstract class AbstractApiReadWriteCRUDController extends AbstractApiReadOnlyCRUDController implements CRUDControllerInterface, ApiCRUDControllerReadWriteInterface, ApiCRUDControllerReadOnlyInterface
{
    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $model = $this->getService()->getRepositoryService()->getById($id);

            if (!$model) {
                return Response::json(
                    [
                        'success' => false,
                        'message' => trans('model.not_found'),
                    ], 202);
            }

            $modelDestroyed = $this->getService()->getRepositoryService()->destroy($id);

            return Response::json(
                [
                    'success' => $modelDestroyed,
                    'message' => $modelDestroyed ? trans('model.deleted') : trans('model.delete_failed'),
                ], $modelDestroyed ? 200 : 202);
        } catch (Exception $exception) {
            Log::error('New '.$this->getService()->getRepositoryService()->getModelName().' Save: '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'failed' => $exception->getMessage(),
                ], 202);
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $model = $this->getService()->getRepositoryService()->save($request->all());
            if ($model->exists) {
                return Response::json(
                    [
                        'model' => $model,
                        'success' => true,
                    ]);
            }
            return Response::json(
                [
                    'success' => false,
                ], 202);
        } catch (Exception $exception) {
            Log::error('New '.$this->getService()->getRepositoryService()->getModelName().' Save: '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'failed' => $exception->getMessage(),
                ], 202);
        }
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $modelUpdated = $this->getService()->getRepositoryService()->update($id, $request->all());
            return Response::json(
                [
                    'success' => $modelUpdated,
                ], $modelUpdated ? 200 : 202);
        } catch (Exception $exception) {
            Log::error('Update '.$this->getService()->getRepositoryService()->getModelName().': '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'failed' => $exception->getMessage(),
                ], 202);
        }
    }
}

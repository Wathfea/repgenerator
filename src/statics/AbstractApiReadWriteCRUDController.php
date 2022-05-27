<?php

namespace App\Abstraction\CRUD\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Response;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Log;

abstract class AbstractApiReadWriteCRUDController extends AbstractCRUDController implements CRUDControllerInterface, ApiCRUDControllerReadWriteInterface, ApiCRUDControllerReadOnlyInterface
{
    /**
     * @var string
     */
    private string $modelRouteName = '';

    /**
     * @param string $modelRouteName
     */
    public function setModelRouteName(string $modelRouteName): void
    {
        $this->modelRouteName = $modelRouteName;
    }

    /**
     * @param string $action
     * @return string
     */
    public function getModelRoute(string $action = 'index'): string
    {
        if ( $this->modelRouteName ) {
            route($this->modelRouteName . '.' . $action);
        }
        return RouteServiceProvider::HOME;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->getIndexData($request));
    }

    /**
     * @param Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse {
        try {
            $model = $this->service->create($request->all());
            if ( $model->exists ) {
                return Response::json(
                    [
                        'model' => $model,
                        'redirect_url' => $this->getModelRoute(),
                        'success' => true,
                    ]);
            }
            return Response::json(
                [
                    'success' => false,
                ], 202);
        } catch (Exception $exception) {
            Log::error('New ' . $this->service->getModelName() . ' Save: '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'failed' => $exception->getMessage(),
                ], 202);
        }
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    /**
     * @param Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $modelUpdated = $this->service->update($id, $request->all());
            return Response::json(
                [
                    'success' => $modelUpdated,
                    'redirect_url' => $this->getModelRoute(),
                ], $modelUpdated ? 200 : 202);
        } catch (Exception $exception) {
            Log::error('Update ' . $this->service->getModelName() . ': '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'failed' => $exception->getMessage(),
                ], 202);
        }
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $model = $this->service->getById($id);

            if(!$model) {
                return Response::json(
                    [
                        'success' => false,
                        'message' => trans('model.not_found'),
                    ], 202);
            }

            $modelDestroyed = $this->service->destroy($id);

            return Response::json(
                [
                    'success' => $modelDestroyed,
                    'message' => $modelDestroyed ? trans('model.deleted') : trans('model.delete_failed'),
                    'redirect_url' => $this->getModelRoute(),
                ], $modelDestroyed ? 200 : 202);
        } catch (Exception $exception) {
            Log::error('New ' . $this->service->getModelName() . ' Save: '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'failed' => $exception->getMessage(),
                ], 202);
        }
    }
}

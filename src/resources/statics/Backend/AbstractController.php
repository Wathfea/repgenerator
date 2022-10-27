<?php

namespace App\Abstraction\Controllers;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Ramsey\Collection\Collection;

abstract class AbstractController implements ControllerInterface, ReadOnlyControllerInterface, ReadWriteControllerInterface
{
    /**
     * @var string
     */
    private string $resourceClass = JsonResource::class;

    /**
     * @var string
     */
    private string $filterClass = BaseQueryFilter::class;

    /**
     * @var int
     */
    private int $perPage = 10;

    /**
     * @var array
     */
    private array $relations = [];

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param  array  $relations
     * @return mixed
     */
    public function setRelations(array $relations): mixed
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * @param  array  $newRelations
     * @return void
     */
    public function addToRelations(array $newRelations): void
    {
        $this->setRelations(array_merge($this->getRelations(), $newRelations));
    }

    /**
     * @param  array  $removableRelations
     * @return void
     */
    public function removeFromRelations(array $removableRelations): void
    {
        $this->setRelations(array_diff($this->getRelations(), $removableRelations));
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
     * @return mixed
     */
    public function setResourceClass(string $resourceClass): mixed
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
     * @return mixed
     */
    public function setFilterClass(string $filterClass): mixed
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
     * @return mixed
     */
    public function setPerPage(int $perPage): mixed
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @param Request $request
     * @return BaseQueryFilter
     */
    public function getFilter(Request $request): BaseQueryFilter {
        return app($this->getFilterClass(), $request->all());
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getLoad(Request $request): array {
        $load = $this->getRelations();
        if ( $request->has('load') && !empty($request->get('load')) ) {
            $load = array_intersect($load, $request->get('load'));
        }
        return $load;
    }

    /**
     * @param Request $request
     * @param Model|null $model
     * @return JsonResponse
     */
    public function getShowResponse(Request $request, Model|null $model): JsonResponse
    {
        if ( !$model ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('model.not_found')
            ], 202);
        }
        if ( Gate::allows('show', $model) ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('auth.insufficient_permissions')
            ], 403);
        }
        /** @var JsonResource $resource */
        $resource = $this->getResourceClass();
        return $resource::make($model)->toResponse($request);
    }

    /**
     * @param Request $request
     * @param callable $listFunction
     * @return JsonResponse|void
     */
    public function getListResponse(Request $request, callable $listFunction) {
        if ( Gate::allows('index') ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('auth.insufficient_permissions')
            ], 403);
        }
        return $listFunction()->toResponse();
    }

    /**
     * @param Request $request
     * @param callable $storeOrAttachFunction
     * @param string|null $validator
     * @return JsonResponse
     */
    public function getStoreOrAttachResponse(Request $request, callable $storeOrAttachFunction, string $validator = null): JsonResponse {
        if ( !empty($validator) ) {
            $request->validate(app($validator)->rules());
        }
        if ( Gate::allows('store') ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('auth.insufficient_permissions')
            ], 403);
        }
        try {
            $model = $storeOrAttachFunction();
            return $this->getShowResponse($request, $model->load($this->getRelations()));
        } catch (Exception $exception) {
            Log::error('New '.$this->getService()->getRepositoryService()->getModelName().' Save: '.$exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 202);
        }
    }

    /**
     * @param Request $request
     * @param callable $getterFunction
     * @param callable $updateFunction
     * @param string|null $validator
     * @return JsonResponse
     */
    public function getUpdateResponse(Request $request, callable $getterFunction, callable $updateFunction, string $validator = null): JsonResponse {
        if ( !empty($validator) ) {
            $request->validate(app($validator)->rules());
        }
        $model = $getterFunction();
        if ( !$model ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('model.not_found')
            ], 202);
        }
        if ( Gate::allows('update', $model) ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('auth.insufficient_permissions')
            ], 403);
        }
        try {
            $modelUpdated = $updateFunction($model);
            return Response::json(
                [
                    'success' => $modelUpdated,
                ], $modelUpdated ? 200 : 202);
        } catch (Exception $exception) {
            Log::error('Update ' . $this->getService()->getRepositoryService()->getModelName() . ': ' . $exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 202);
        }
    }

    /**
     * @param Request $request
     * @param callable $getterFunction
     * @param callable $destroyOrDetachFunction
     * @param string $validator
     * @return JsonResponse
     */
    public function getDestroyOrDetachResponse(Request $request, callable $getterFunction, callable $destroyOrDetachFunction, string $validator): JsonResponse {
        if ( !empty($validator) ) {
            $request->validate(app($validator)->rules());
        }
        $model = $getterFunction();
        if ( !$model ) {
            return Response::json(
                [
                    'success' => false,
                    'message' => trans('model.not_found'),
                ], 202);
        }
        if ( Gate::allows('destroy', $model) ) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('auth.insufficient_permissions')
            ], 403);
        }
        try {
            $modelDestroyed = $destroyOrDetachFunction($model);
            return Response::json(
                [
                    'success' => $modelDestroyed,
                    'message' => $modelDestroyed ? trans('model.deleted') : trans('model.delete_failed'),
                ], $modelDestroyed ? 200 : 202);
        } catch (Exception $exception) {
            Log::error('Delete ' . $this->getService()->getRepositoryService()->getModelName() . ' Error: ' . $exception->getMessage());
            return Response::json(
                [
                    'success' => false,
                    'message' => trans('model.being_used'),
                    'error' => $exception->getMessage(),
                ], 202);
        }
    }
}

<?php

namespace App\Abstraction\CRUD\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Response;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Log;

abstract class AbstractApiReadOnlyCRUDController extends AbstractCRUDController implements CRUDControllerInterface, ApiCRUDControllerReadOnlyInterface
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
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }
}

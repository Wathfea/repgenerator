<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

abstract class AbstractBladeReadOnlyCRUDController extends AbstractFrontendReadOnlyCRUDController implements CRUDControllerInterface, BladeCRUDControllerReadOnlyInterface
{
    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function index(Request $request): Factory|View|Application
    {
        return view($this->getIndexView(), $this->getData([
            'data' => $this->getIndexData($request),
        ]));
    }

    /**
     * @param  int  $id
     * @return Factory|View|Application
     */
    public function show($id): Factory|View|Application
    {
        return view($this->getShowView(), $this->getData([
            'model' => $this->service->getById($id)
        ]));
    }
}

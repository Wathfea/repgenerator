<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

abstract class AbstractBladeReadWriteCRUDController extends AbstractFrontendReadWriteCRUDController implements CRUDControllerInterface, BladeCRUDControllerReadWriteInterface, BladeCRUDControllerReadOnlyInterface
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
     * @param Request $request
     * @return Factory|View|Application
     */
    public function create(Request $request): Factory|View|Application
    {
        return view($this->getCreateView(), $this->getData());
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


    /**
     * @param Request $request
     * @param int $id
     * @return Factory|View|Application
     */
    public function edit(Request $request, int $id) : Factory|View|Application
    {
        return view($this->getEditView(), $this->getData([
            'data' => $this->getEditData($id),
        ]));
    }
}

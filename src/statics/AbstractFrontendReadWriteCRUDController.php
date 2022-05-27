<?php

namespace App\Abstraction\CRUD\Controllers;

use App\Abstraction\CRUD\Enums\CRUDConfigType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

abstract class AbstractFrontendReadWriteCRUDController extends AbstractCRUDController implements CRUDControllerInterface, FrontendCRUDControllerReadWriteInterface, FrontendCRUDControllerReadOnlyInterface
{
    protected string $deleteRedirectRoute = 'dashboard';
    protected string $storeRedirectRoute = 'dashboard';

    /**
     * @param string $deleteRedirectRoute
     * @return AbstractFrontendReadWriteCRUDController
     */
    public function setDeleteRedirectRoute(string $deleteRedirectRoute): AbstractFrontendReadWriteCRUDController
    {
        $this->deleteRedirectRoute = $deleteRedirectRoute;
        return $this;
    }

    /**
     * @param string $storeRedirectRoute
     * @return AbstractFrontendReadWriteCRUDController
     */
    public function setStoreRedirectRoute(string $storeRedirectRoute): AbstractFrontendReadWriteCRUDController
    {
        $this->storeRedirectRoute = $storeRedirectRoute;
        return $this;
    }

    /**
     * @param string $fallback
     * @return string
     */
    public function getView(string $fallback): string {
        return $this->getConfig(CRUDConfigType::VIEW, $fallback);
    }

    /**
     * @return string
     */
    public function getIndexView(): string {
        return $this->getView('CRUD/index');
    }

    /**
     * @return string
     */
    public function getCreateView(): string {
        return $this->getView( 'CRUD/create');
    }

    /**
     * @return string
     */
    public function getShowView(): string {
        return $this->getView( 'CRUD/show');
    }

    /**
     * @return string
     */
    public function getEditView(): string {
        return $this->getView('CRUD/edit');
    }

    /**
     * @param Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $model = $this->service->create($request->all());
        return $request->has('redirect') ? Redirect::to($request->get('redirect')) : Redirect::route($this->storeRedirectRoute)
            ->with('success', $model?->exists)
            ->with('model', $model);
    }

    /**
     * @param Request  $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $updateRequest = $this->getConfig(CRUDConfigType::UPDATE_REQUEST);
        if ( $updateRequest ) {
            Validator::validate($request->all(), app($updateRequest)::createFrom($request)->rules());
        }
        return Redirect::back()->with('success', $this->service->update($id, $request->all()));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        return $request->has('redirect') ?
            Redirect::to($request->get('redirect')) :
            Redirect::route($this->deleteRedirectRoute)
                ->with('success', $this->service->destroy($id));
    }
}

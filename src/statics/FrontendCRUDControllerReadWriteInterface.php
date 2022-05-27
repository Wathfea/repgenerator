<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface FrontendCRUDControllerReadWriteInterface extends CRUDControllerInterface
{
    public function getView(string $fallback): string;
    public function getIndexView(): string;
    public function getEditView(): string;
    public function getCreateView(): string;
    public function getShowView(): string;
    public function store(Request $request): RedirectResponse;
    public function update(Request $request, $id): RedirectResponse;
    public function destroy(Request $request, $id): RedirectResponse;
    public function setDeleteRedirectRoute(string $deleteRedirectRoute): FrontendCRUDControllerReadWriteInterface;
    public function setStoreRedirectRoute(string $storeRedirectRoute): FrontendCRUDControllerReadWriteInterface;
}

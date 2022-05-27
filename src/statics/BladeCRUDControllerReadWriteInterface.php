<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\Request;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

interface BladeCRUDControllerReadWriteInterface extends CRUDControllerInterface, FrontendCRUDControllerReadWriteInterface
{
    public function index(Request $request): Factory|View|Application;
    public function create(Request $request): Factory|View|Application;
    public function show($id): Factory|View|Application;
    public function edit(Request $request, int $id) : Factory|View|Application;
}

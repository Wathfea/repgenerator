<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\Request;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

interface BladeCRUDControllerReadOnlyInterface extends CRUDControllerInterface, FrontendCRUDControllerReadOnlyInterface
{
    public function index(Request $request): Factory|View|Application;
    public function show($id): Factory|View|Application;
}

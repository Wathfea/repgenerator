<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface ApiCRUDControllerReadOnlyInterface extends CRUDControllerInterface
{
    public function index(Request $request): JsonResponse;
    public function show($id): JsonResponse;
}

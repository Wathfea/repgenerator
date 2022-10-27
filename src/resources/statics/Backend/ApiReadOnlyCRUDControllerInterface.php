<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadOnlyControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiReadOnlyCRUDControllerInterface extends ControllerInterface, ReadOnlyControllerInterface, CRUDControllerInterface
{
    public function show(Request $request, int $id): JsonResponse;
    public function index(Request $request): JsonResponse;
}

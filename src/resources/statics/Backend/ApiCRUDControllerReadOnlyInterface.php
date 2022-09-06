<?php

namespace App\Abstraction\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiCRUDControllerReadOnlyInterface extends CRUDControllerInterface
{
    public function index(Request $request, array $relationships = []): JsonResponse;

    public function show(Request $request, int $id): JsonResponse;
}

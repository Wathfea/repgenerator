<?php

namespace App\Abstraction\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface ApiCRUDControllerReadOnlyInterface extends CRUDControllerInterface
{
    public function index(Request $request, array $relationships = []): JsonResponse;
    public function show(Request $request, int $id): JsonResponse;
}

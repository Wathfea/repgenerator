<?php

namespace App\Abstraction\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiCRUDControllerReadWriteInterface extends CRUDControllerInterface
{
    public function destroy(Request $request, int $id): JsonResponse;

    public function store(Request $request): JsonResponse;

    public function update(Request $request, int $id): JsonResponse;
}

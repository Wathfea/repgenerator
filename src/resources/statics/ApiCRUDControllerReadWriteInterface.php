<?php

namespace App\Abstraction\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiCRUDControllerReadWriteInterface extends CRUDControllerInterface
{
    public function destroy($id): JsonResponse;

    public function store(Request $request): JsonResponse;

    public function update(Request $request, $id): JsonResponse;
}

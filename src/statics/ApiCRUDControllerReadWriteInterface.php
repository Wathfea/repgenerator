<?php

namespace App\Abstraction\CRUD\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface ApiCRUDControllerReadWriteInterface extends CRUDControllerInterface
{
    public function store(Request $request): JsonResponse;
    public function update(Request $request, $id): JsonResponse;
    public function destroy($id): JsonResponse;
}

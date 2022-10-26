<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadWriteControllerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiReadWriteCRUDControllerInterface extends ControllerInterface, ReadWriteControllerInterface, CRUDControllerInterface, ApiReadOnlyCRUDControllerInterface
{
    public function handleStore(Request $request): Model;
    public function handleUpdate(Request $request, Model $model): bool;
    public function handleDestroy(Request $request, Model $model): bool;

    public function destroy(Request $request, int $id): JsonResponse;
    public function store(Request $request): JsonResponse;
    public function update(Request $request, int $id): JsonResponse;
}

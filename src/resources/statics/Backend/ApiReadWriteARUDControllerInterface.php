<?php

namespace App\Abstraction\Controllers\ARUD;

use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadWriteControllerInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiReadWriteARUDControllerInterface extends ControllerInterface, ReadWriteControllerInterface, ARUDControllerInterface, ApiReadOnlyARUDControllerInterface
{
    public function handleAttach(Request $request, int $parentId, int $relationId): Pivot;
    public function handleDetach(Request $request, Pivot $pivot, int $parentId, int $relationId): bool;
    public function handleUpdate(Request $request, Pivot $pivot, int $parentId, int $relationId): bool;

    public function detach(Request $request, int $parentId, int $relationId): JsonResponse;
    public function attach(Request $request, int $parentId, int $relationId): JsonResponse;
    public function update(Request $request, int $parentId, int $relationId): JsonResponse;
}

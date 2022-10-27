<?php

namespace App\Abstraction\Controllers\ARUD;

use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadOnlyControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiReadOnlyARUDControllerInterface extends ControllerInterface, ReadOnlyControllerInterface, ARUDControllerInterface
{
    public function show(Request $request, int $parentId, int $relationId): JsonResponse;
}

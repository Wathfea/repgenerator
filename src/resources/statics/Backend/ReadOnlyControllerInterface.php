<?php

namespace App\Abstraction\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ReadOnlyControllerInterface extends ControllerInterface
{
    public function getShowResponse(Request $request, Model $model): JsonResponse;
}

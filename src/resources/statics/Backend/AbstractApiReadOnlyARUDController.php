<?php

namespace App\Abstraction\Controllers\ARUD;

use App\Abstraction\Controllers\AbstractController;
use App\Abstraction\Controllers\ControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractApiReadOnlyARUDController extends AbstractController implements ControllerInterface, ARUDControllerInterface, ApiReadOnlyARUDControllerInterface
{
    /**
     * @param Request $request
     * @param int $parentId
     * @param int $relationId
     * @param array $relationships
     * @return JsonResponse
     */
    public function show(Request $request, int $parentId, int $relationId, array $relationships = []): JsonResponse
    {
        $this->addToRelations($relationships);
        $pivot = $this->getService()->getRepositoryService()->getSpecific($parentId, $relationId);
        return $this->getShowResponse($request, $pivot);
    }
}

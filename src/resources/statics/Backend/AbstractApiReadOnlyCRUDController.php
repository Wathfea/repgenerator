<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Controllers\AbstractController;
use App\Abstraction\Controllers\ControllerInterface;
use App\Abstraction\Controllers\ReadOnlyControllerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractApiReadOnlyCRUDController extends AbstractController implements ControllerInterface, ReadOnlyControllerInterface, CRUDControllerInterface, ApiReadOnlyCRUDControllerInterface
{
    /** @var string  */
    protected string $showRequest = '';

    /**
     * @param string $showRequest
     * @return AbstractApiReadOnlyCRUDController
     */
    public function setShowRequest(string $showRequest): AbstractApiReadOnlyCRUDController
    {
        $this->showRequest = $showRequest;
        return $this;
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @param  array  $relationships
     * @return JsonResponse
     */
    public function show(Request $request, int $id, array $relationships = []): JsonResponse
    {
        if ( !empty($this->showRequest) ) {
            $request->validate(app($this->showRequest)->rules());
        }
        $this->addToRelations($relationships);
        $model = $this->getService()->getRepositoryService()->getById($id, $this->getRelations());
        return $this->getShowResponse($request, $model);
    }
}

<?php

namespace App\Abstraction\Controllers\CRUD;

use App\Abstraction\Repository\HasModelRepositoryService;

interface CRUDControllerInterface
{
    public function getService(): HasModelRepositoryService;
}

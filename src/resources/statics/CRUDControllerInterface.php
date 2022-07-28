<?php

namespace App\Abstraction\Controllers;

use App\Abstraction\Repository\HasRepositoryService;

interface CRUDControllerInterface
{
    public function getService(): HasRepositoryService;
}

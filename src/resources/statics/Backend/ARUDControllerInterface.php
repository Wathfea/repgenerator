<?php

namespace App\Abstraction\Controllers\ARUD;

use App\Abstraction\Repository\HasPivotRepositoryService;

interface ARUDControllerInterface
{
    public function getService(): HasPivotRepositoryService;
}

<?php

namespace App\Abstraction\Repository;

interface HasPivotRepositoryService extends HasRepositoryService
{
    public function getRepositoryService(): PivotRepositoryServiceInterface;
}

<?php

namespace App\Abstraction\Repository;

interface HasModelRepositoryService extends HasRepositoryService
{
    public function getRepositoryService(): ModelRepositoryServiceInterface;
}

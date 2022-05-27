<?php

namespace App\Abstraction\Repository\Service;

use App\Abstraction\Repository\RepositoryInterface;

interface RepositoryServiceInterface
{
    /**
     * @param  RepositoryInterface  $repository
     */
    public function __construct(RepositoryInterface $repository);

    /**
     * @param  int  $id
     */
    public function exists(int $id);

    /**
     * @return string
     */
    public function getModelName(): string;
}

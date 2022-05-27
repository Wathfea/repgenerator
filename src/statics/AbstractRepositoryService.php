<?php

namespace App\Abstraction\Repository\Service;

use App\Abstraction\Repository\RepositoryInterface;

abstract class AbstractRepositoryService implements RepositoryServiceInterface
{
    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * @param  RepositoryInterface  $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->repository->getModelName();
    }
}

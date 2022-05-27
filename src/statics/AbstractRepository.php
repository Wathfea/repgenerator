<?php

namespace App\Abstraction\Repository;

/**
 * Class AbstractRepository.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * AbstractRepository constructor.
     * @param  string  $modelName
     */
    public function __construct(protected string $modelName)
    {
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }
}

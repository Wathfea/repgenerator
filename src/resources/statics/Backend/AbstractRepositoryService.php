<?php

namespace App\Abstraction\Repository;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractRepository.
 */
abstract class AbstractRepositoryService implements RepositoryServiceInterface
{
    /**
     * AbstractRepository constructor.
     * @param  string  $model
     */
    public function __construct(protected string $model)
    {
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return new $this->model;
    }
}

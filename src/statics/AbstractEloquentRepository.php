<?php

namespace App\Abstraction\Repository\Eloquent;

use App\Abstraction\Repository\AbstractRepository;
use App\Abstraction\Repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractEloquentRepository.
 */
abstract class AbstractEloquentRepository extends AbstractRepository implements RepositoryInterface, EloquentRepositoryInterface
{
    /**
     * AbstractEloquentRepository constructor.
     * @param  Model  $model
     */
    public function __construct(protected Model $model)
    {
        $modelClass = explode('\\', $model);
        parent::__construct( strtolower($modelClass[count($modelClass)-1]));
    }
}

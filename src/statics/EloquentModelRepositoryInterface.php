<?php

namespace App\Abstraction\Repository\Eloquent\Model;

use App\Abstraction\Repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use App\Abstraction\Repository\ModelRepositoryInterface;

/**
 * Interface EloquentRepositoryInterface.
 */
interface EloquentModelRepositoryInterface extends RepositoryInterface, ModelRepositoryInterface
{
    /**
     * @param  array  $load
     * @return Builder
     */
    public function getQB(array $load): Builder;


}

<?php

namespace App\Abstraction\Repository;

use App\Abstraction\Filter\BaseQueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface RepositoryInterface.
 */
interface RepositoryServiceInterface
{

    /**
     * @param  array  $columns
     * @param  array  $load
     * @return Model|null
     */
    public function getFirstByColumns(array $columns, array $load = []): Model|null;

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @return Model|null
     */
    public function getFirstByColumn(string $column, mixed $value, array $load = []): Model|null;

    /**
     * @param  array  $columns
     * @param  array  $load
     * @return Builder|Collection
     */
    public function getAllByColumns(array $columns, array $load = []): Collection|Builder;

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @return array|Collection
     */
    public function getAllByColumn(string $column, mixed $value, array $load = []): array|Collection;



    /**
     * @param  array  $load
     * @param  int|null  $perPage
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll(array $load = [], int $perPage = null): Collection|LengthAwarePaginator;
}

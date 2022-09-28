<?php

namespace App\Abstraction\Repository;


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
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @return Builder|Collection
     */
    public function getAllByColumns(array $columns, array $load = [], string $orderBy = 'id', string $orderDir = 'asc'): Collection|Builder;

    /**
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $load
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @return array|Collection
     */
    public function getAllByColumn(string $column, mixed $value, array $load = [], string $orderBy = 'id', string $orderDir = 'asc'): array|Collection;
}

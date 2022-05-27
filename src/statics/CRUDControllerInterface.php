<?php

namespace App\Abstraction\CRUD\Controllers;

interface CRUDControllerInterface
{
    public function setCRUDConfig(array $config): CRUDControllerInterface;
    public function getConfig( string $config, mixed $default = null): mixed;
    public function getLoad(): array;
    public function getData(array $data) : array;
}

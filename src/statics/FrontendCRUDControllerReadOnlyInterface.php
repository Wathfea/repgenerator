<?php

namespace App\Abstraction\CRUD\Controllers;


interface FrontendCRUDControllerReadOnlyInterface extends CRUDControllerInterface
{
    public function getView(string $fallback): string;
    public function getIndexView(): string;
    public function getShowView(): string;
}

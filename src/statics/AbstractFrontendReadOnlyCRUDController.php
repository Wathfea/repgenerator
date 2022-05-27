<?php

namespace App\Abstraction\CRUD\Controllers;

use App\Abstraction\CRUD\Enums\CRUDConfigType;


abstract class AbstractFrontendReadOnlyCRUDController extends AbstractCRUDController implements CRUDControllerInterface, FrontendCRUDControllerReadOnlyInterface
{
    /**
     * @param string $fallback
     * @return string
     */
    public function getView(string $fallback): string {
        return $this->getConfig(CRUDConfigType::VIEW, $fallback);
    }

    /**
     * @return string
     */
    public function getIndexView(): string {
        return $this->getView('CRUD/index');
    }


    /**
     * @return string
     */
    public function getShowView(): string {
        return $this->getView( 'CRUD/show');
    }
}

<?php

namespace App\Abstraction\CRUD\Enums;

use BenSampo\Enum\Enum;

final class CRUDConfigType extends Enum
{
    const VIEW = 'view';
    const LOAD = 'load';
    const PAGINATE = 'paginate';
    const EXTRA_DATA = 'extra_data';
    const RESOURCE = 'resource';
    const UPDATE_REQUEST = 'update_request';
    const CREATE_REQUEST = 'store_request';
}

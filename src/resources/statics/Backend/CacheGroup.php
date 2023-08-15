<?php

namespace App\Abstraction\Cache;

use App\Abstraction\Models\BaseModel;


class CacheGroup extends BaseModel
{
    const NAME_COLUMN = 'name';
    const KEY_COLUMN = 'key';
}

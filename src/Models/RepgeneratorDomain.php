<?php

namespace Pentacom\Repgenerator\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $model
 * @property json $meta
 */
class RepgeneratorDomain extends Model
{
    protected $fillable = ['model', 'meta'];
}

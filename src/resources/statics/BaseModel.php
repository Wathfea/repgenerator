<?php

namespace App\Abstraction\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionException;

/**
 * App\Models\BaseModel
 *
 * @method static Builder|BaseModel newModelQuery()
 * @method static Builder|BaseModel newQuery()
 * @method static Builder|BaseModel query()
 * @method static Builder|BaseModel withAll()
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    /**
     * @param $query
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function scopeWithAll($query): mixed
    {
        return $query->with($this->getSupportedRelations());
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function getSupportedRelations(): array
    {
        $relations = [];
        $reflextionClass = new ReflectionClass(get_called_class());

        foreach ($reflextionClass->getMethods() as $method) {
            $doc = $method->getDocComment();

            if ($doc && str_contains($doc, '@Relation')) {
                $relations[] = $method->getName();
            }
        }

        return $relations;
    }
}

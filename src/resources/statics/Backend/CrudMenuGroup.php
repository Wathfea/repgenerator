<?php

namespace App\Domain\CrudMenuGroup\Models;

use App\Abstraction\Models\BaseModel;
use App\Domain\CrudMenu\Models\CrudMenu;
use App\Domain\CrudMenuGroup\Factories\CrudMenuGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kblais\QueryFilter\Filterable;

class CrudMenuGroup extends BaseModel
{
    use HasFactory, Filterable;

    const NAME_COLUMN = "name";
    const ICON_COLUMN = "icon";
    const ORDER_COLUMN = "order";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        self::NAME_COLUMN,
        self::ICON_COLUMN,
        self::ORDER_COLUMN,
    ];

    protected static function newFactory(): CrudMenuGroupFactory
    {
        return CrudMenuGroupFactory::new();
    }

    /**
     * @Relation
     *
     * @return HasMany
     */
    public function crudMenus(): HasMany
    {
        return $this->hasMany(CrudMenu::class, 'crud_menu_group_id', 'id');
    }
}

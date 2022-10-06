<?php

namespace App\Domain\CrudMenu\Models;

use App\Abstraction\Models\BaseModel;
use App\Domain\CrudMenu\Factories\CrudMenuFactory;
use App\Domain\CrudMenuGroup\Models\CrudMenuGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kblais\QueryFilter\Filterable;

class CrudMenu extends BaseModel
{
    use HasFactory, Filterable;

    const NAME_COLUMN = "name";
    const URL_COLUMN = "url";
    const ICON_COLUMN = "icon";
    const CRUD_MENU_GROUP_ID_COLUMN = "crud_menu_group_id";
    const CREATED_AT_COLUMN = "created_at";
    const UPDATED_AT_COLUMN = "updated_at";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        self::NAME_COLUMN,
        self::URL_COLUMN,
        self::ICON_COLUMN,
        self::CRUD_MENU_GROUP_ID_COLUMN,
        self::CREATED_AT_COLUMN,
        self::UPDATED_AT_COLUMN,
    ];

    protected static function newFactory(): CrudMenuFactory
    {
        return CrudMenuFactory::new();
    }

    /**
     * @Relation
     *
     * @return BelongsTo
     */
    public function crudMenuGroup(): BelongsTo
    {
        return $this->belongsTo(CrudMenuGroup::class, 'crud_menu_group_id', 'id');
    }
}

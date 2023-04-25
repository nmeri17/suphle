<?php

namespace Suphle\Adapters\Orms\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\{Factory, HasFactory};

abstract class BaseModel extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    abstract protected static function newFactory(): Factory; // we can't use a common interface for all adapters, since they'll have different way of setting factories

    public static function __callStatic($method, $parameters)
    {

        return null;
    }

    /**
     * Allows us group feature-related migrations across multiple models/tables together
    */
    abstract public static function migrationFolders(): array;
}

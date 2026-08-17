<?php

namespace _database_namespace_;

use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetToken extends BaseModel
{
    public const TABLE_NAME = "password_reset_tokens";

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function migrationFolders(): array
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
    }
}
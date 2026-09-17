<?php

namespace _database_namespace;

use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;

use _database_namespace\Factories\PasswordResetTokenFactory;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetToken extends BaseModel
{
    public const TABLE_NAME = "password_reset_tokens";

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): Factory
    {

        return PasswordResetTokenFactory::new();
    }

    public static function migrationFolders(): array
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
    }
}
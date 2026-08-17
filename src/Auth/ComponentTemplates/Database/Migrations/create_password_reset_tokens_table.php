<?php
namespace _database_namespace_\Migrations;

use _database_namespace_\{PasswordResetToken, User};

use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(PasswordResetToken::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

            $table->string('token')->unique();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(PasswordResetToken::TABLE_NAME);
    }
};
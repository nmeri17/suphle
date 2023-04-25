<?php

namespace Suphle\Tests\Mocks\Models\Eloquent\Migrations;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {

        Schema::create("employer", function (Blueprint $table) {

            $table->id();

            $table->foreignIdFor(EloquentUser::class);

            $table->timestamps();
        });
    }

    public function down(): void
    {

        Schema::drop("employer");
    }
};

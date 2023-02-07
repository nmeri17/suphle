<?php
	namespace Suphle\Adapters\Orms\Eloquent\Migrations;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("edit_history", function (Blueprint $table) {

				$table->id();

				$table->morphs("historical");

				$table->foreignIdFor(EloquentUser::class);

				$table->json("payload");

				$table->timestampsTz();
			});
		}

		public function down ():void {

			Schema::drop("edit_history");
		}
	};
?>
<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Migrations;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	class CreateTableEditHistory extends Migration {

		public function up ():void {

			Schema::create("edit_history", function (Blueprint $table) {

				$table->id();

				$table->morphs("historical");

				$table->foreignIdFor(User::class);

				$table->json("payload");

				$table->timestampsTz();
			});
		}

		public function down ():void {

			Schema::drop("edit_history");
		}
	}
?>
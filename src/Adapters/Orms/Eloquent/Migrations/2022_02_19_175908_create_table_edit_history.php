<?php
	namespace Suphle\Adapters\Orms\Eloquent\Migrations;

	use Suphle\Adapters\Orms\Eloquent\Models\User;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

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
	};
?>
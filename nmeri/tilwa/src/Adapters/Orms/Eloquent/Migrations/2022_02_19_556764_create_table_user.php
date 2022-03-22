<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Migrations;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	class CreateTableUser extends Migration {

		public function up ():void {

			Schema::create("users", function (Blueprint $table) {

				$table->id();

				$table->string("email", 70)->unique();

				$table->timestamp("email_verified_at");

				$table->string("password", 90);

				$table->timestampsTz();
			});
		}

		public function down ():void {

			Schema::drop("user");
		}
	}
?>
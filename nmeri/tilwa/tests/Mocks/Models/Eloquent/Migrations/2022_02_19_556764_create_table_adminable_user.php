<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Migrations;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("adminable_user", function (Blueprint $table) {

				$table->id();

				$table->string("email", 70)->unique();

				$table->timestamp("email_verified_at");

				$table->string("password", 90);

				$table->boolean("is_admin")->default(false);

				$table->timestampsTz();
			});
		}

		public function down ():void {

			Schema::drop("adminable_user");
		}
	};
?>
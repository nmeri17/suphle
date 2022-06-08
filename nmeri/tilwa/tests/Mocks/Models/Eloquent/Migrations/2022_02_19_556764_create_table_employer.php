<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Migrations;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("employer", function (Blueprint $table) {

				$table->id();

				$table->foreignIdFor(User::class);

				$table->timestamps();
			});
		}

		public function down ():void {

			Schema::drop("employer");
		}
	};
?>
<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Migrations;

	use Tilwa\Tests\Mocks\Models\Eloquent\Models\Employer;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("employment", function (Blueprint $table) {

				$table->id();

				$table->enum("status", ["taken", "available"])->default("available");

				$table->foreignIdFor(Employer::class);

				$table->timestamps();
			});
		}

		public function down ():void {

			Schema::drop("employment");
		}
	};
?>
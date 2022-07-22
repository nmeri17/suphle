<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent\Migrations;

	use Suphle\Tests\Mocks\Models\Eloquent\Employer;

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
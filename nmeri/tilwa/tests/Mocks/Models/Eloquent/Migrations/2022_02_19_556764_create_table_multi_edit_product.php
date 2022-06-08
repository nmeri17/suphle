<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Migrations;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("multi_edit_product", function (Blueprint $table) {

				$table->id();

				$table->string("name", 150);

				$table->integer("price");

				$table->timestamps();
			});
		}

		public function down ():void {

			Schema::drop("multi_edit_product");
		}
	};
?>
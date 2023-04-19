<?php
	namespace _database_namespace\Migrations;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("_resource_name", function (Blueprint $table) {

				$table->id();

				$table->string("title");

				$table->timestamps();
			});
		}

		public function down ():void {

			Schema::drop("_resource_name");
		}
	};
?>
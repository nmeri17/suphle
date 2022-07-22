<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent\Migrations;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Tests\Mocks\Models\Eloquent\AdminableUser;

	use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		public function up ():void {

			Schema::create("multi_edit_product", function (Blueprint $table) {

				$table->id();

				$table->string("name", 150);

				$table->integer("price");

				$table->foreignIdFor(EloquentUser::class, "seller_id");

				$table->timestamps();
			});
		}

		public function down ():void {

			Schema::drop("multi_edit_product");
		}
	};
?>
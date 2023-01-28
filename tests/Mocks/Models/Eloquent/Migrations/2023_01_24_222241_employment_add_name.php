<?php

use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

use Illuminate\Support\Facades\Schema;

class EmploymentAddName extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ():void {

		Schema::table("employment", function (Blueprint $table) {

			$table->string("title", 160);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropColumns("employment", ["title"]);
	}
}

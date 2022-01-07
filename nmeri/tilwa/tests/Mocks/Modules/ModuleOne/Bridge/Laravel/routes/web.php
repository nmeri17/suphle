<?php

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\Controllers\HomeController;

	Route::get("/laravel/entry", [HomeController::class, "entry"]);
?>
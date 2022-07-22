<?php

	use Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\Controllers\HomeController;

	Route::get("/laravel/entry", [HomeController::class, "entry"]);
?>
<?php

	use Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\Controllers\HomeController;

	Route::get("/laravel/entry", (new HomeController())->entry(...));
?>

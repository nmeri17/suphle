<?php
	namespace Tilwa\Bridge\Laravel;

	use Illuminate\Foundation\Application;

	use Tilwa\Contracts\LaravelApp;

	class LaravelAppConcrete extends Application implements LaravelApp {

		// if it still complains about absence of those methods, create concretes that simply invoke the parent
	}
?>
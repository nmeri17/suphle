<?php
	namespace Tilwa\Bridge\Laravel;

	use Illuminate\Foundation\Application;

	use Tilwa\Contracts\Bridge\LaravelContainer;

	class LaravelAppConcrete extends Application implements LaravelContainer {

		// if it still complains about absence of those methods, create concretes that simply invoke the parent
	}
?>
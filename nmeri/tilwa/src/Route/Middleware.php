<?php
	
	namespace Tilwa\Routes;

	use Tilwa\Controllers\Bootstrap;

	class Middleware {

		protected $prevData;

		protected $app;

		/**
		* @param {$prevData}:mixed Passed from previous middleware's `next()`. Initialized with Nmeri\Tilwa\Routes\Route
		*/
		function __construct( Bootstrap $app, $prevData ) {

			$this->prevData = $prevData;

			$this->app = $app;
		}

		public function handle (Closure $next, ...$args ) {

			// perform some logic here, then
			return $next($this->prevData);
		}
	}
?>
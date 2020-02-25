<?php
	
	namespace Tilwa\Routes;

	use Tilwa\Controllers\Bootstrap;

	class Middleware {

		/**
		* @property Callable */
		public $postSourceBehavior; // used To enable operations after Source is obtained. Receives the app

		protected $app;

		function __construct( Bootstrap $app ) {

			$this->app = $app;
		}

		/**
		* @description: mutate app container
		*
		* @return false to quit middleware stack
		*/
		public function handle ( array $args ) {

			return true;// perform some logic here with app and args
		}
	}
?>
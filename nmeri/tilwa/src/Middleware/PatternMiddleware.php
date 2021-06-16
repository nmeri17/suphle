<?php
	namespace Tilwa\Middleware;

	use Tilwa\Contracts\Middleware;

	class PatternMiddleware {

		private $middlewareList = [];

		public function addMiddleware (Middleware $instance) {

			$this->middlewareList[] = $instance;
		}

		public function getList ():array {

			return $this->middlewareList;
		}
	}
?>
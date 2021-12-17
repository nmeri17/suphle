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

		public function omitWherePresent (Middleware $toOmit):void {

			foreach ($this->middlewareList as $index => $existing)

				if ($existing == $toOmit) // losse comparison

					unset($this->middlewareList[$index]);
		}
	}
?>
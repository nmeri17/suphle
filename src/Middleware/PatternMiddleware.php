<?php
	namespace Suphle\Middleware;

	class PatternMiddleware {

		protected array $middlewareList = [];

		/**
		 * Defer their hydration since not all middleware are guaranteed to run on each request. We're using strings cuz we wanna control their hydration. We don't want to pollute the route collection with their possible dependencies
		 * 
		 * @param {name} Middleware class name
		*/
		public function addMiddleware (string $name) {

			$this->middlewareList[] = $name;
		}

		public function getList ():array {

			return $this->middlewareList;
		}

		public function setList (array $middlewares):void {

			$this->middlewareList = $middlewares;
		}

		public function omitWherePresent (array $toOmit):void {

			foreach ($toOmit as $middlewareName) {

				$index = array_search($middlewareName, $this->middlewareList);

				if ($index !== false)

					unset($this->middlewareList[$index]);
			}
		}
	}
?>
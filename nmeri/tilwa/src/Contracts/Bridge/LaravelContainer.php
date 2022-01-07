<?php
	namespace Tilwa\Contracts\Bridge;

	interface LaravelContainer {

		public function getBindings():array;

		public function bind($abstract, $concrete = null, $shared = false):void;

		public function make($abstract, array $parameters = []);
	}
?>

			
<?php

	namespace Tilwa\Contracts;

	interface LaravelApp {

		public function getBindings():array;

		public function bind($abstract, $concrete = null, $shared = false):void;
	}
?>

			
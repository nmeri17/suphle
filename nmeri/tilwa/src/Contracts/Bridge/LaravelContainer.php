<?php
	namespace Tilwa\Contracts\Bridge;

	interface LaravelContainer {

		/**
		 * @return array
		*/
		public function getBindings();

		/**
		 * @return void
		*/
		public function bind($abstract, $concrete = null, $shared = false);

		public function make($abstract, array $parameters = []);

		public function instance($abstract, $instance);

		public function defaultBindings ():array;

		public function injectBindings (array $bindings):void;

		public function runContainerBootstrappers ():void;
	}
?>

			
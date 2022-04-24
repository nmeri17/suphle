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

		public function concreteBinds ():array;

		public function simpleBinds ():array;

		public function registerConcreteBindings (array $bindings):void;

		public function registerSimpleBindings (array $bindings):void;

		public function runContainerBootstrappers ():void;

		public function createSandbox (callable $explosive);
	}
?>

			
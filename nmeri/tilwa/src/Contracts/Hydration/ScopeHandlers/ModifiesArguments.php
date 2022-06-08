<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	interface ModifiesArguments {

		/**
		 * @param {arguments} mixed[]. Method argument list
		*/
		public function transformConstructor (object $dummyInstance, array $arguments):array;

		/**
		 * @param {arguments} mixed[]. Method argument list
		*/
		public function transformMethods (object $concreteInstance, array $arguments):array;
	}
?>
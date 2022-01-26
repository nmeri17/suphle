<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	interface ModifiesArguments {

		public function transformConstructor ($dummyInstance, array $arguments):array;

		public function transformMethods ($concreteInstance, array $arguments):array;
	}
?>
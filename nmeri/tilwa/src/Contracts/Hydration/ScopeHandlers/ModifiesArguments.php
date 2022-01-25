<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	use Tilwa\Hydration\Templates\AvoidConstructor;

	interface ModifiesArguments {

		public function transformConstructor (AvoidConstructor $dummyInstance, array $arguments):array;

		public function transformMethods ($concreteInstance, array $arguments):array;
	}
?>
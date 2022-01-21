<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	interface ModifiesArguments {

		public function transformList ($dummyInstance, array $arguments):array;
	}
?>
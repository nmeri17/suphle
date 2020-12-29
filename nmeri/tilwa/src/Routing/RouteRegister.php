<?php

	namespace Tilwa\Routing;

	class RouteRegister {

		private $prefixMode;

		private $namespaceMode;

		private $apiMode;

		public function crud (string $basePath, string $controller, array $overrides ) {

			// there should be an overwriteable heuristic for determining whether view for a requested exists and to return that or JSON (along with what controller action we're calling)

			$resourceTemplates = []; // showCreateForm, saveNew, showAll, showOne, update, delete

			// foreach ($resourceTemplates)
		}
	}
?>
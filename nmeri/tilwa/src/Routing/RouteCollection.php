<?php

	namespace Tilwa\Routing;

	class RouteCollection {

		// should be called in the api entry class so subsequent methods found within the same scope can overwrite methods attending to browser
		public function mirrorBrowserRoutes () {

			// duplicate browser routes, but change the anchor key to "api"
		}

		public function crud (string $basePath, string $controller, array $overrides ) {

			// there should be an overwriteable heuristic for determining whether view for a requested exists and to return that or JSON (along with what controller action we're calling)

			$resourceTemplates = []; // showCreateForm, saveNew, showAll, showOne, update, delete

			// foreach ($resourceTemplates)
		}

		public function get (Route $route) {

			// register
		}

		public function post (Route $route) {

			// register
		}

		public function delete (Route $route) {

			// register
		}

		public function put (Route $route) {

			// register
		}

		private function register($route) {

			$route->assignMethod();

			$route->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;
		}

		public function prefixFor (string $routeClass) {

			// load it somewhere for recurser to pick up?
		}
	}
?>
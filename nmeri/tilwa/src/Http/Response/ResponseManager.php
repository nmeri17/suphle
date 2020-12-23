<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\Bootstrap;

	use Tilwa\Routing\{Route, RouteManager};

	class ResponseManager {

		private $app;

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}
		
		public function getResponse () {

			$router = $this->app->router;

			$arguments = $router->prepareArguments();

			return $this->getValidRoute($router)
			
			->execute($arguments)->renderResponse();
		}

		private function getValidRoute (RouteManager $router):Route {

			$route = $router->getActiveRoute();

			$request = $route->getRequest();

			if (!$request || !$request->isValidated())

				$route = $router->mergeWithPrevious($request);

			else /*if ($this->app->getClass(Tilwa\Contracts\Auth)->name !== "browser")*/

				$router->setPrevious($route); // uncomment when that is implemented

			return $route;
		}
	}
?>
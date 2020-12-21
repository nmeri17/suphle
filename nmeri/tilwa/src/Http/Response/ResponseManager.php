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

			$route = $this->getValidRoute($router);
			
			$arguments = $router->setActiveRoute($route)

			->prepareArguments();

			return $route->execute($arguments)

			->renderResponse();
		}

		private function getValidRoute (RouteManager $router):Route {

			$route = $router->getActiveRoute();

			$request = $route->getRequest(); // this should throw an error if no route is found

			if (!$request->isValidated())

				$route = $router->mergeWithPrevious($request);

			else /*if ($this->app->getClass(Tilwa\Contracts\Auth)->name !== "browser")*/

				$router->setPrevious($route); // uncomment when that is implemented

			return $route;
		}
	}
?>
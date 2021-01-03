<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\Bootstrap;

	use Tilwa\Routing\{Route, RouteManager};

	use Tilwa\Http\Request\Authenticator;

	class ResponseManager {

		private $app;

		private $router;

		private $skipHandler;

		function __construct (Bootstrap $app, RouteManager $router ) {

			$this->app = $app;

			$this->router = $router;
		}
		
		public function getResponse () {

			$arguments = $this->router->prepareArguments();

			$route = $this->getValidRoute();

			if (!$this->skipHandler)

				$route->execute($arguments);
			
			return $route->renderResponse();
		}

		/** @description
		*	For requests originating from browser, flow will be reverted to previous request, expecting its view to read the error bag
		*	For other clients, the handler should be skipped altogether for the errors to be immediately rendered
		*/
		private function getValidRoute ():Route {

			$route = $this->router->getActiveRoute();

			$request = $route->getRequest();

			$browserOrigin = $this->app->getClass(Authenticator::class)->fromBrowser();

			if ( !$request->isValidated()) {

				if ($browserOrigin)

					$route = $this->router->mergeWithPrevious($request);
				
				else $this->skipHandler = true;
			}
			else if ($browserOrigin)

				$this->router->setPrevious($route);

			return $route;
		}
	}
?>
<?php

	namespace Tilwa\Http\Response;

	use Tilwa\module\Bootstrap;

	use Tilwa\Routing\{Route, RouteManager};

	class ResponseManager {

		private $module;

		private $router;

		private $skipHandler;

		public $responseMutations;

		function __construct (Bootstrap $module, RouteManager $router ) {

			$this->module = $module;

			$this->router = $router;

			$this->responseMutations = [];
		}
		
		public function getResponse () {

			$arguments = $this->router->prepareArguments();

			$route = $this->getValidRoute();

			if (!$this->skipHandler) {

				$this->runMiddleware($route);

				$route->execute($arguments);
			}

			$body = $route->render();
			
			if (!$this->skipHandler)
				
				$body = $this->mutateResponse($body);
			
			return $body;
		}

		/** @description
		*	For requests originating from browser, flow will be reverted to previous request, expecting its view to read the error bag
		*	For other clients, the handler should be skipped altogether for the errors to be immediately rendered
		*/
		private function getValidRoute ():Route {

			$route = $this->router->getActiveRoute();

			$request = $route->getRequest();

			$browserOrigin = !$this->router->isApiRoute();

			if ( !$request->isValidated()) {

				if ($browserOrigin)

					$route = $this->router->mergeWithPrevious($request);
				
				else $this->skipHandler = true;
			}
			else if ($browserOrigin)

				$this->router->setPrevious($route);

			return $route;
		}

		// middleware delimited by commas. Middleware parameters delimited by colons
		private function runMiddleware ( Route $route ):bool {

			$passed = true;

			foreach ($route->getMiddlewares() as $mw ) {

				@[$className, $args] = explode(',', $mw);

				$instance = $this->module->getClass($className);

				if (is_callable($instance->postSourceBehavior))

					$this->responseMutations[] = $instance->postSourceBehavior;

				else $passed = $instance->handle( explode(':', $args) );

				if ( !$passed ) return $passed; // terminate
			}

			return $passed;
		}

		private function mutateResponse(string $currentBody):string {

			foreach ($this->responseMutations as $handler)

				$currentBody = $handler($currentBody);
			
			return $currentBody;
		}
	}
?>
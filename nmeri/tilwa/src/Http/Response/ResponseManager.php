<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\Container;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Http\Response\Format\Markup;

	class ResponseManager {

		private $container;

		private $router;

		private $skipHandler;

		public $responseMutations;

		function __construct (Container $container, RouteManager $router ) {

			$this->container = $container;

			$this->router = $router;

			$this->responseMutations = [];
		}
		
		public function getResponse () {

			$this->router->prepareArguments();

			$renderer = $this->getValidRenderer();

			if (!$this->skipHandler) {

				$this->runMiddleware($renderer);

				if ($renderer instanceof Markup)

					$renderer->wantsJson($this->router->acceptsJson());

				$renderer->execute($this->handlerParameters);
			}

			$body = $renderer->render();
			
			if (!$this->skipHandler)
				
				$body = $this->mutateResponse($body);
			
			return $body;
		}

		/** @description
		*	For requests originating from browser, flow will be reverted to previous request, expecting its view to read the error bag
		*	For other clients, the handler should be skipped altogether for the errors to be immediately rendered
		*/
		private function getValidRenderer ():AbstractRenderer {

			$renderer = $this->router->getActiveRenderer();

			$request = $renderer->getRequest();

			$browserOrigin = !$this->router->isApiRoute();

			if ( !$request->isValidated()) {

				if ($browserOrigin)

					$renderer = $this->router->mergeWithPrevious($request);
				
				else $this->skipHandler = true;
			}
			else if ($browserOrigin)

				$this->router->setPrevious($renderer);

			return $renderer;
		}

		// middleware delimited by commas. Middleware parameters delimited by colons
		private function runMiddleware ( AbstractRenderer $renderer ):bool {

			$passed = true;

			foreach ($renderer->getMiddlewares() as $mw ) {

				@[$className, $args] = explode(',', $mw);

				$instance = $this->container->getClass($className);

				if (is_callable($instance->postSourceBehavior))

					$this->responseMutations[] = $instance->postSourceBehavior;

				else $passed = $instance->handle( explode(':', $args) );

				if ( !$passed ) return $passed; // terminate
			}

			return $passed;
		}

		// last action before response is flushed. log or profile middleware goes here
		private function mutateResponse(string $currentBody):string {

			foreach ($this->responseMutations as $handler)

				$currentBody = $handler($currentBody);
			
			return $currentBody;
		}
	}
?>
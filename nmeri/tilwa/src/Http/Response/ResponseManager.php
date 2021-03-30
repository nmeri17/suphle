<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\{Container, ParentModule};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Http\Response\Format\Markup;

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Controllers\ControllerManager;

	class ResponseManager extends BaseResponseManager {

		private $container;

		private $router;

		private $skipHandler; // is `true` on validation failure

		public $responseMutations;

		function __construct (Container $container, RouteManager $router ) {

			$this->container = $container;

			$this->router = $router;

			$this->responseMutations = [];
		}
		
		public function getResponse () {

			$oldRenderer = $this->router->getActiveRenderer();

			$request = $oldRenderer->getRequest();

			$renderer = $this->getValidRenderer($oldRenderer, $request);

			if (!$this->skipHandler) {

				$controllerManager = $this->getControllerManager($renderer);

				$this->validateManager($controllerManager);

				$this->buildManagerTarget($controllerManager, $renderer);

				if ($renderer instanceof Markup && $this->router->acceptsJson())

					$renderer->setWantsJson();

				$this->runMiddleware($renderer); // called here so some awesome middleware can override default behavior on our booted controller

				$renderer->invokeActionHandler($controllerManager->getHandlerParameters());
			}

			$body = $renderer->render();
			
			if (!$this->skipHandler)
				
				$body = $this->mutateResponse($body);
			
			return $body;
		}

		/** @description: Validates request and decides whether controller will be invoked
		*	For requests originating from browser, flow will be reverted to previous request, expecting its view to read the error bag
		*	For other clients, the handler should be skipped altogether for the errors to be immediately rendered
		*/
		private function getValidRenderer (AbstractRenderer $currentRenderer, BaseRequest $request):AbstractRenderer {

			$browserOrigin = !$this->router->isApiRoute();

			if ( !$request->isValidated()) {

				if ($browserOrigin)

					$currentRenderer = $this->router->mergeWithPrevious($request);
				
				else $this->skipHandler = true;
			}
			else if ($browserOrigin)

				$this->router->setPrevious($currentRenderer);

			return $currentRenderer;
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

		public function validateManager(ControllerManager $manager):void {

			$globalDependencies = $this->container->getClass(ParentModule::class)->getDependsOn();

			$manager->validateController($globalDependencies);
		}

		public function buildManagerTarget(ControllerManager $manager, AbstractRenderer $renderer):void {

			$manager->bootController();

			$manager->setHandlerParameters($renderer->handler);

			$manager->provideModelArguments($renderer->getRequest(), $renderer->routeMethod);
		}
	}
?>
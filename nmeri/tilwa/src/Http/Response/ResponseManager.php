<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\{Container, ParentModule};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Http\Response\Format\Markup;

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Controllers\ControllerManager;

	use Tilwa\Contracts\ResponseManager as ManagerInterface;

	class ResponseManager implements ManagerInterface {

		private $container;

		private $router;

		private $skipHandler; // is `true` on validation failure
		private $controllerManager;

		public $responseMutations;

		private $renderer;

		function __construct (Container $container, RouteManager $router, ControllerManager $controllerManager) {

			$this->container = $container;

			$this->router = $router;

			$this->responseMutations = [];

			$this->controllerManager = $controllerManager;
		}
		
		public function setValidRenderer ():void {

			$oldRenderer = $this->router->getActiveRenderer();

			$request = $oldRenderer->getRequest();

			$this->renderer = $this->getValidRenderer($oldRenderer, $request);
		}
		
		public function getResponse ():string {

			if (!$this->skipHandler) $this->handleValidRequest();

			$body = $renderer->render();
			
			if (!$this->skipHandler)
				
				$body = $this->mutateResponse($body); // those middleware should only get the response object/headers, not our payload
			
			return $body;
		}

		public function afterEvaluation() {

			if ($this->renderer->hasBranches()) // the very first request won't be caught in a flow. so, delegate queueing branches

				$this->renderer->queueNextFlow();
		}

		private function handleValidRequest() {

			$this->updateControllerManager();

			$this->validateManager();

			$this->buildManagerTarget();

			$renderer = $this->renderer;

			if ($renderer instanceof Markup && $this->router->acceptsJson())

				$renderer->setWantsJson();

			$this->runMiddleware(); // called here so some awesome middleware can override default behavior on our booted controller

			$renderer->invokeActionHandler($this->controllerManager->getHandlerParameters());
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
		private function runMiddleware ():bool {

			$passed = true;

			foreach ($this->renderer->getMiddlewares() as $mw ) {

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

		public function validateManager():void {

			$globalDependencies = $this->container->getClass(ParentModule::class)->getDependsOn();

			$this->controllerManager->validateController($globalDependencies);
		}

		public function buildManagerTarget():void {

			$manager = $this->controllerManager;

			$renderer = $this->renderer;

			$manager->bootController();

			$manager->setHandlerParameters($renderer->handler);

			$manager->provideModelArguments($renderer->getRequest(), $renderer->routeMethod);
		}

		private function updateControllerManager():void {

			$this->controllerManager->setController(

				$this->container->getClass($this->renderer->getController())
			);
		}
	}
?>
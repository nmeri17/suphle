<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\{Container, ModuleDescriptor};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Http\Response\Format\{Markup, AbstractRenderer};

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Controllers\ControllerManager;

	use Tilwa\Contracts\{BaseResponseManager, QueueManager};

	use Tilwa\Flows\{Structures\BranchesContext, Jobs\RouteBranches}

	class ResponseManager implements BaseResponseManager {

		private $container, $router, $renderer,

		$queueManager, $authenticator, $controllerManager;

		private $skipHandler = false; // is `true` on validation failure

		public $responseMutations = [];

		function __construct (Container $container, RouteManager $router, ControllerManager $controllerManager, QueueManager $queueManager, Authenticator $authenticator) {

			$this->container = $container;

			$this->router = $router;

			$this->controllerManager = $controllerManager;

			$this->queueManager = $queueManager;

			$this->authenticator = $authenticator;
		}

		public function rendererValidationFailed():bool {
			
			return $this->skipHandler == true;
		}
		
		public function getResponse ():string {

			return $this->renderer->render();
		}

		public function afterRender() {

			if ($this->renderer->hasBranches()) { // the very first request won't be caught in a flow. so, delegate queueing branches

				$user = $this->authenticator->getUser();

				$this->queueManager->push(RouteBranches::class,
					new BranchesContext(null, $user, $this->renderer, $this )
				);
			}

			return $finalBody;
		}

		public function bootControllerManager():self {

			$this->updateControllerManager();

			$this->validateManager();

			$this->buildManagerTarget();

			return $this;
		}

		public function handleValidRequest():AbstractRenderer {

			$renderer = $this->renderer;

			if ($renderer instanceof Markup && $this->router->acceptsJson())

				$renderer->setWantsJson();

			$manager = $this->controllerManager;

			$manager->updatePlaceholders()

			->hydrateModels($renderer->getRouteMethod());

			$this->runMiddleware(); // called here so some awesome middleware can override default behavior on our booted controller. may imply injecting the manager

			return $renderer->invokeActionHandler($manager->getHandlerParameters());
		}

		/** @description: Validates request and decides whether controller will be invoked
		*	For requests originating from browser, flow will be reverted to previous request, expecting its view to read the error bag
		*	For other clients, the handler should be skipped altogether for the errors to be immediately rendered
		*/
		public function assignValidRenderer ():void {

			$router = $this->router;

			$manager = $this->controllerManager;

			$outgoingRenderer = $router->getActiveRenderer();

			$browserOrigin = !$router->isApiRoute();

			$request = $manager->getRequest();

			if ( !$request->isValidated()) {

				if ($browserOrigin) {

					$outgoingRenderer = $router->getPreviousRenderer();

					$manager->revertRequest($router->getPreviousRequest());
				}

				else $this->skipHandler = true;
			}

			else if ($browserOrigin)

				$router->setPrevious($outgoingRenderer, $request);

			$this->renderer = $outgoingRenderer;
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
		public function mutateResponse(string $currentBody):string {

			foreach ($this->responseMutations as $handler)

				$currentBody = $handler($currentBody);
			
			return $currentBody;
		}

		public function validateManager():void {

			$globalDependencies = $this->container->getClass(ModuleDescriptor::class)->getDependsOn();

			$this->controllerManager->validateController($globalDependencies);
		}

		public function buildManagerTarget():void {

			$this->controllerManager->bootController()

			->setHandlerParameters($this->renderer->handler)

			->assignActionRequest() // this should run before model hydration and before validation

			->assignModelsInAction();
		}

		private function updateControllerManager():void {

			$this->controllerManager->setController(

				$this->container->getClass($this->renderer->getController())
			);
		}

		public function getControllerManager():ControllerManager {
			
			return $this->controllerManager;
		}
	}
?>
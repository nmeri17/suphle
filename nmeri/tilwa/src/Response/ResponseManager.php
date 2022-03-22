<?php
	namespace Tilwa\Response;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Routing\{RouteManager, RequestDetails};

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Contracts\{Requests\BaseResponseManager, Presentation\BaseRenderer};

	use Tilwa\Request\{ValidatorManager, PayloadStorage};

	use Tilwa\Exception\Explosives\ValidationFailure;

	class ResponseManager implements BaseResponseManager {

		private $container, $router, $renderer, $requestDetails,

		$controllerManager, $flowQueuer;

		function __construct (Container $container, RouteManager $router, CoodinatorManager $controllerManager, FlowResponseQueuer $flowQueuer, BaseRenderer $renderer, RequestDetails $requestDetails) {

			$this->container = $container;

			$this->router = $router;

			$this->controllerManager = $controllerManager;

			$this->flowQueuer = $flowQueuer;

			$this->renderer = $renderer;

			$this->requestDetails = $requestDetails;
		}
		
		public function getResponse ():string {

			return $this->renderer->render();
		}

		public function afterRender ($data):void {

			if ($this->renderer->hasBranches())// the very first request won't be caught in a flow. so, delegate queueing branches

				$this->flowQueuer->insert($this->renderer, $this);
		}

		public function bootCoodinatorManager ():self {

			$this->controllerManager->setDependencies (

				$this->container->getClass($this->renderer->getController()),

				$this->renderer->getHandler()
			)->bootController();

			return $this;
		}

		public function handleValidRequest (PayloadStorage $payloadStorage):BaseRenderer {

			$renderer = $this->renderer;

			if (!$this->requestDetails->isApiRoute())

				$this->router->setPreviousRenderer($renderer);

			return $renderer->invokeActionHandler($this->controllerManager->getHandlerParameters());
		}

		public function isValidRequest ():bool {

			return $this->controllerManager->hasValidatorErrors();
		}

		public function mayBeInvalid ():void {

			if (!$this->isValidRequest())

				throw new ValidationFailure($this->controllerManager);
		}

		public function requestAuthenticationStatus (AuthStorage $storage):bool {

			$storage->resumeSession();

			return !is_null($storage->getUser()); // confirms there's an active session and that its owner exists on the underlying database
		}
	}
?>
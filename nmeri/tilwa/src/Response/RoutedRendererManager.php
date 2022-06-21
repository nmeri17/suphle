<?php
	namespace Tilwa\Response;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Contracts\{Requests\BaseResponseManager, Presentation\BaseRenderer};

	use Tilwa\Request\{ValidatorManager, PayloadStorage, RequestDetails};

	use Tilwa\Exception\Explosives\ValidationFailure;

	class RoutedRendererManager implements BaseResponseManager {

		private $container, $router, $renderer, $requestDetails,

		$controllerManager, $flowQueuer;

		public function __construct (
			BaseRenderer $renderer, Container $container,

			RouteManager $router, CoodinatorManager $controllerManager,

			FlowResponseQueuer $flowQueuer, RequestDetails $requestDetails
		) {

			$this->container = $container;

			$this->router = $router;

			$this->controllerManager = $controllerManager;

			$this->flowQueuer = $flowQueuer;

			$this->renderer = $renderer;

			$this->requestDetails = $requestDetails;
		}

		public function responseRenderer ():BaseRenderer {

			return $this->renderer;
		}

		public function afterRender ($data = null):void {

			if ($this->renderer->hasBranches())// the first organic request needs to trigger the flows below it

				$this->flowQueuer->saveSubBranches($this->renderer);
		}

		public function bootCoodinatorManager ():self {

			$this->controllerManager->setDependencies (

				$this->renderer->getController(),

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

	}
?>
<?php
	namespace Suphle\Response;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Hydration\Container;

	use Suphle\Routing\RouteManager;

	use Suphle\Services\Decorators\BindsAsSingleton;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Contracts\Requests\{BaseResponseManager, CoodinatorManager};

	use Suphle\Request\{ValidatorManager, PayloadStorage, RequestDetails};

	use Suphle\Exception\Explosives\ValidationFailure;

	#[BindsAsSingleton]
	class RoutedRendererManager implements BaseResponseManager {
			
		protected readonly BaseRenderer $renderer;

		public function __construct (

			protected readonly Container $container,

			protected readonly RouteManager $router,

			protected readonly CoodinatorManager $controllerManager,

			protected readonly FlowResponseQueuer $flowQueuer,

			protected readonly RequestDetails $requestDetails
		) {

			$this->renderer = $router->getActiveRenderer();
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

				$this->renderer->getCoordinator(),

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
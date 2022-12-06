<?php
	namespace Suphle\Response;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Hydration\Container;

	use Suphle\Routing\RouteManager;

	use Suphle\Services\{CoodinatorManager, Decorators\BindsAsSingleton};

	use Suphle\Contracts\{Requests\BaseResponseManager, Presentation\BaseRenderer};

	use Suphle\Request\{ValidatorManager, PayloadStorage, RequestDetails};

	use Suphle\Exception\Explosives\ValidationFailure;

	#[BindsAsSingleton]
	class RoutedRendererManager implements BaseResponseManager {

		public function __construct (
			private readonly BaseRenderer $renderer,

			private readonly Container $container,

			private readonly RouteManager $router,

			private readonly CoodinatorManager $controllerManager,

			private readonly FlowResponseQueuer $flowQueuer,

			private readonly RequestDetails $requestDetails
		) {

			//
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
<?php
	namespace Suphle\Response;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Hydration\Container;

	use Suphle\Services\Decorators\BindsAsSingleton;

	use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};

	use Suphle\Contracts\Requests\{BaseResponseManager, CoodinatorManager};

	use Suphle\Request\{ PayloadStorage, RequestDetails};

	use Suphle\Exception\Explosives\ValidationFailure;

	#[BindsAsSingleton]
	class RoutedRendererManager implements BaseResponseManager {
			
		protected readonly BaseRenderer $renderer;

		public function __construct (

			protected readonly BaseRenderer $renderer,

			protected readonly Session $sessionClient,

			protected readonly CoodinatorManager $controllerManager,

			protected readonly FlowResponseQueuer $flowQueuer,

			protected readonly RequestDetails $requestDetails
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

			$this->coordinatorManager->setDependencies (

				$this->renderer->getCoordinator(),

				$this->renderer->getHandler()
			)->bootCoordinator();

			return $this;
		}

		public function handleValidRequest (PayloadStorage $payloadStorage):BaseRenderer {

			if ($this->shouldStoreRenderer())

				$this->sessionClient->setValue(PreviousResponse::PREVIOUS_GET_RENDERER, $this->renderer); // store this before invocation since PDO objects are unserialiable, and before parsing since that would prevent possible merging on next request

			return $this->renderer->invokeActionHandler(

				$this->coordinatorManager->getHandlerParameters()
			);
		}

		protected function shouldStoreRenderer ():bool {

			return $this->requestDetails->isGetRequest() &&

			!$this->requestDetails->isApiRoute();
		}

		/**
		 * Expected to be used when renderer is derived from other source other than the router. That source should have run all relevant protocols preceding coordinator execution
		*/
		public function bypassRendererProtocols (BaseRenderer $renderer):void {

			$this->coordinatorManager->setDependencies (

				$renderer->getCoordinator(), $renderer->getHandler()
			)->bootCoordinator();

			$renderer->invokeActionHandler(

				$this->coordinatorManager->getHandlerParameters()
			);
		}

		public function isValidRequest ():bool {

			return $this->coordinatorManager->hasValidatorErrors();
		}

		public function mayBeInvalid ():void {

			if (!$this->isValidRequest())

				throw new ValidationFailure($this->coordinatorManager);
		}

	}
?>
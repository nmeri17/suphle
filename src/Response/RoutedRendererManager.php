<?php
	namespace Suphle\Response;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Hydration\Container;

	use Suphle\Services\{ServiceCoordinator, Decorators\BindsAsSingleton};

	use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session, Requests\ValidationEvaluator};

	use Suphle\Contracts\Response\{BaseResponseManager, RendererManager};

	use Suphle\Request\{ PayloadStorage, RequestDetails, ValidatorManager};

	use Suphle\Exception\Explosives\{ValidationFailure, Generic\NoCompatibleValidator};

	#[BindsAsSingleton(RendererManager::class)]
	class RoutedRendererManager implements RendererManager, BaseResponseManager, ValidationEvaluator {

		public const PREVIOUS_GET_RENDERER = "previous_get_renderer";

		protected array $handlerParameters;
			
		public function __construct (

			protected readonly Container $container,

			protected readonly BaseRenderer $renderer,

			protected readonly Session $sessionClient,

			protected readonly FlowResponseQueuer $flowQueuer,

			protected readonly RequestDetails $requestDetails,

			protected readonly ValidatorManager $validatorManager
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

		public function bootDefaultRenderer ():self {

			$this->handlerParameters = $this->fetchHandlerParameters(
				$this->renderer->getCoordinator(),

				$this->renderer->getHandler()
			);

			return $this;
		}

		public function handleValidRequest (PayloadStorage $payloadStorage):BaseRenderer {

			if ($this->shouldStoreRenderer())

				$this->sessionClient->setValue(self::PREVIOUS_GET_RENDERER, $this->renderer); // store this before invocation since PDO objects are unserialiable, and before parsing since that would prevent possible merging on next request

			return $this->renderer->invokeActionHandler($this->handlerParameters);
		}

		protected function shouldStoreRenderer ():bool {

			return $this->requestDetails->isGetRequest() &&

			!$this->requestDetails->isApiRoute();
		}

		/**
		 * Checks for whether current or previous should be renedered, depending on currently active renderer
		 * 
		 * Expects current request to contain same placeholder values used for executing that preceding request
		*/
		public function invokePreviousRenderer (array $toMerge = []):?BaseRenderer {

			if (!$this->renderer->deferValidationContent()) // if current request is something like json, write validation errors to it

				$previousRenderer = $this->renderer;
			else {

				$previousRenderer = $this->sessionClient->getValue(self::PREVIOUS_GET_RENDERER);

				if (!$previousRenderer) return null;

				$this->bypassOrganicProcedures($previousRenderer);
			}
			
			$previousRenderer->forceArrayShape($toMerge);

			return $previousRenderer;
		}

		/**
		 * Expected to be used when renderer is derived from other source other than the router. That source should have run all relevant protocols preceding coordinator execution
		*/
		public function bypassOrganicProcedures (BaseRenderer $renderer):void {
			
			$renderer->invokeActionHandler($this->fetchHandlerParameters(
				
				$renderer->getCoordinator(), $renderer->getHandler()
			));
		}

		public function fetchHandlerParameters (

			ServiceCoordinator $coodinator, string $handlingMethod
		):array {

			return $this->container

			->getMethodParameters($handlingMethod, $coodinator::class);
		}

		/**
		 * {@inheritdoc}
		*/
		public function mayBeInvalid (?BaseRenderer $renderer = null):self {

			if (is_null($renderer)) $renderer = $this->renderer;
			
			$shouldValidate = $this->acquireValidatorStatus(

				$renderer->getCoordinator(), $renderer->getHandler()
			);

			if ($shouldValidate && !$this->validatorManager->isValidated())

				throw new ValidationFailure($this);

			return $this;
		}

		/**
		 * {@inheritdoc}
		*/
		public function acquireValidatorStatus (ServiceCoordinator $coodinator, string $handlingMethod):bool {

			$collectionName = $coodinator->validatorCollection();

			if ($this->eligibleToValidate(

				$coodinator::class, $handlingMethod, $collectionName
			)) {

				$this->validatorManager->setActionRules(

					call_user_func([

						$this->container->getClass($collectionName),

						$handlingMethod
					])
				);

				return true;
			}

			return false;
		}

		/**
		 * @param {collectionName} The validation collection
		*/
		protected function eligibleToValidate (

			string $coodinatorName, string $handlingMethod,

			?string $collectionName
		):bool { 

			$hasNoValidator = empty($collectionName) ||

			!method_exists($collectionName, $handlingMethod);

			if ($hasNoValidator) {

				if ($this->requestDetails->isGetRequest()) return false;

				throw new NoCompatibleValidator(

					$coodinatorName, $handlingMethod
				);
			}

			return true;
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function validationRenderer (array $failureDetails):BaseRenderer {

			return $this->invokePreviousRenderer($failureDetails);
		}
	}
?>
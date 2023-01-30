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

			$this->bootCoordinator(
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
		 * Expected to be used when renderer is derived from other source other than the router. That source should have run all relevant protocols preceding coordinator execution
		*/
		public function bypassOrganicProcedures (BaseRenderer $renderer, bool $skipValidation = false):void {
			
			$this->bootCoordinator(
				
				$renderer->getCoordinator(), $renderer->getHandler(),

				$skipValidation
			);

			$renderer->invokeActionHandler($this->handlerParameters);
		}

		/**
		 * {@inheritdoc}
		*/
		public function mayBeInvalid ():void {

			if (!$this->validatorManager->isValidated())

				throw new ValidationFailure($this);
		}

		/**
		 * Checks for whether current or previous should be renedered, depending on currently active renderer
		 * 
		 * Expects current request to contain same placeholder values used for executing that preceding request
		*/
		public function invokePreviousRenderer (array $toMerge = []):BaseRenderer {

			if (!$this->renderer->deferValidationContent()) // if current request is something like json, write validation errors to it

				return $this->renderer;

			$previousRenderer = $this->sessionClient->getValue(self::PREVIOUS_GET_RENDERER);

			$this->bypassOrganicProcedures($previousRenderer, true); // safe to disable since renderer was stored on success i.e. must have passed its validation
			
			$previousRenderer->forceArrayShape($toMerge);

			return $previousRenderer;
		}

		public function bootCoordinator (ServiceCoordinator $coodinator, string $handlingMethod, bool $skipValidation = false):void {
			
			if (!$skipValidation)

				$this->updateValidatorMethod($coodinator, $handlingMethod);

			$this->handlerParameters = $this->container

			->getMethodParameters($handlingMethod, $coodinator::class);
		}

		/**
		 * {@inheritdoc}
		*/
		public function updateValidatorMethod (ServiceCoordinator $coodinator, string $handlingMethod):void {

			$collectionName = $coodinator->validatorCollection();

			$hasNoValidator = empty($collectionName) ||

			!method_exists($collectionName, $handlingMethod);

			if ($hasNoValidator) {

				if ($this->requestDetails->isGetRequest()) return;

				throw new NoCompatibleValidator(

					$coodinator::class, $handlingMethod
				);
			}

			$this->validatorManager->setActionRules(

				call_user_func([

					$this->container->getClass($collectionName),

					$handlingMethod
				])
			);
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function validationRenderer (array $failureDetails):BaseRenderer {

			return $this->invokePreviousRenderer($failureDetails);
		}
	}
?>
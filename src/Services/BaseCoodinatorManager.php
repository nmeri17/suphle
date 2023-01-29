<?php
	namespace Suphle\Services;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\{Requests\CoodinatorManager, Presentation\BaseRenderer};

	use Suphle\Request\{ValidatorManager, RequestDetails};

	use Suphle\Routing\RouteManager;

	use Suphle\Response\PreviousResponse;

	use Suphle\Exception\Explosives\Generic\NoCompatibleValidator;

	class BaseCoodinatorManager implements CoodinatorManager {

		protected ServiceCoordinator $coodinator;

		protected string $actionMethod;

		protected array $handlerParameters;

		public function __construct(

			protected readonly Container $container,

			protected readonly ValidatorManager $validatorManager,

			protected readonly RequestDetails $requestDetails,

			protected readonly PreviousResponse $previousResponse
		) {

			//
		}

		public function setDependencies (ServiceCoordinator $coodinator, string $actionMethod):self {
			
			$this->coodinator = $coodinator;

			$this->actionMethod = $actionMethod;

			return $this;
		}

		/**
		 * {@inheritdoc}
		*/
		public function bootCoordinator ():void {

			$this->updateValidatorMethod();

			$this->setHandlerParameters();
		}

		public function updateValidatorMethod ():void {

			$collectionName = $this->coodinator->validatorCollection();

			$hasNoValidator = empty($collectionName) ||

			!method_exists($collectionName, $this->actionMethod);

			if ($hasNoValidator) {

				if ($this->requestDetails->isGetRequest()) return;

				throw new NoCompatibleValidator(

					$this->coodinator::class, $this->actionMethod
				);
			}

			$this->validatorManager->setActionRules(

				call_user_func([

					$this->container->getClass($collectionName),

					$this->actionMethod
				])
			);
		}

		public function setHandlerParameters ():void {

			$this->handlerParameters = $this->container->getMethodParameters(

				$this->actionMethod, $this->coodinator::class
			);
		}

		public function getHandlerParameters():array {

			return $this->handlerParameters;
		}

		public function hasValidatorErrors ():bool {

			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function validationRenderer (array $failureDetails):BaseRenderer {

			$this->previousResponse->invokeRenderer($failureDetails);
		}
	}
?>
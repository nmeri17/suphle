<?php
	namespace Suphle\Adapters\Presentation\Hotwire;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Requests\{CoodinatorManager, ValidationFailureConvention};

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Request\{ValidatorManager, RequestDetails};

	use Suphle\Routing\RouteManager;

	use Suphle\Exception\Explosives\Generic\NoCompatibleValidator;

	class HotwireCoodinatorManager extends BaseCoodinatorManager {

		public function __construct(

			protected readonly Container $container,

			protected readonly ValidatorManager $validatorManager,

			protected readonly RequestDetails $requestDetails,

			protected readonly RouteManager $router,

			protected readonly ValidationFailureConvention $failureConvention
		) {

			//
		}

		public function validationRenderer ():BaseRenderer {

			if (!$this->requestDetails->isApiRoute())

				return $this->failureConvention->deriveFormPartial();

			return $this->router->getActiveRenderer();
		}
	}
?>
<?php
	namespace Suphle\Adapters\Presentation\Hotwire;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Requests\{CoodinatorManager, ValidationFailureConvention};

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Services\BaseCoodinatorManager;

	use Suphle\Request\{ValidatorManager, RequestDetails};

	use Suphle\Routing\RouteManager;

	use Suphle\Response\PreviousResponse;

	use Suphle\Exception\Explosives\Generic\NoCompatibleValidator;

	class HotwireCoodinatorManager extends BaseCoodinatorManager {

		public function __construct(

			protected readonly Container $container,

			protected readonly ValidatorManager $validatorManager,

			protected readonly RequestDetails $requestDetails,

			protected readonly PreviousResponse $previousResponse,

			protected readonly ValidationFailureConvention $failureConvention,

			protected readonly BaseRenderer $renderer
		) {

			//
		}

		public function validationRenderer (array $failureDetails):BaseRenderer {

			if (!$this->renderer instanceof BaseHotwireStream)

				return $this->previousResponse->invokeRenderer($failureDetails);

			return $this->failureConvention->deriveFormPartial($this->renderer, $failureDetails);
		}
	}
?>
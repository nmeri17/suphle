<?php
	namespace Suphle\Adapters\Presentation\Hotwire;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\{IO\Session, Requests\ValidationFailureConvention, Presentation\BaseRenderer};

	use Suphle\Response\{FlowResponseQueuer, RoutedRendererManager};

	use Suphle\Request\{ValidatorManager, RequestDetails};

	use Suphle\Adapters\Presentation\Hotwire\Formats\BaseHotwireStream;

	class HotwireRendererManager extends RoutedRendererManager {

		public function __construct(

			protected readonly Container $container,

			protected readonly BaseRenderer $renderer,

			protected readonly Session $sessionClient,

			protected readonly FlowResponseQueuer $flowQueuer,

			protected readonly RequestDetails $requestDetails,

			protected readonly ValidatorManager $validatorManager,

			protected readonly ValidationFailureConvention $failureConvention
		) {

			//
		}

		public function validationRenderer (array $failureDetails):BaseRenderer {

			if (!$this->renderer instanceof BaseHotwireStream)

				return $this->invokePreviousRenderer($failureDetails);

			return $this->failureConvention

			->deriveFormPartial($this->renderer, $failureDetails);
		}
	}
?>
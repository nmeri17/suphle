<?php
	namespace Suphle\Adapters\Presentation\Hotwire\FailureConventions;

	use Suphle\Contracts\{Presentation\BaseRenderer, Requests\ValidationFailureConvention};

	use Suphle\Response\Format\Hotwire\{ReloadHotwireStream, BaseHotwireStream};

	use Suphle\Request\RequestDetails;

	use Suphle\Routing\RouteManager;

	use Suphle\Response\PreviousResponse;

	class HttpMethodValidationConvention implements ValidationFailureConvention {

		public function __construct (

			protected readonly RouteManager $router,

			protected readonly RequestDetails $requestDetails,

			protected readonly PreviousResponse $previousResponse
		) {

			//
		}

		public function deriveFormPartial ():BaseRenderer {

			$currentRenderer = $this->router->getActiveRenderer();

			if (!$currentRenderer instanceof BaseHotwireStream)

				return $this->previousResponse->getRenderer();

			if ($this->requestDetails->isPostRequest())

				return $currentRenderer->retainCreateNodes();

			return $currentRenderer->retainUpdateNodes();
		}
	}
?>
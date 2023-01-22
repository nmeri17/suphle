<?php
	namespace Suphle\Adapters\Presentation\Hotwire\FailureConventions;

	use Suphle\Contracts\{Presentation\BaseRenderer, Requests\ValidationFailureConvention};

	use Suphle\Response\Format\Hotwire\{ReloadHotwireStream, BaseHotwireStream};

	use Suphle\Request\RequestDetails;

	use Suphle\Routing\RouteManager;

	class HttpMethodValidationConvention implements ValidationFailureConvention {

		public function __construct (

			protected readonly RouteManager $router,

			protected readonly RequestDetails $requestDetails
		) {

			//
		}

		public function deriveFormPartial ():BaseRenderer {

			$currentRenderer = $this->router->getActiveRenderer();

			if (!$currentRenderer instanceof BaseHotwireStream)

				return $this->router->getPreviousRenderer();

			if ($this->requestDetails->isPostRequest())

				return $currentRenderer->retainCreateNodes();

			return $currentRenderer->retainUpdateNodes();
		}
	}
?>
<?php
	namespace Suphle\Adapters\Presentation\Hotwire\FailureConventions;

	use Suphle\Contracts\{Presentation\BaseRenderer, Requests\ValidationFailureConvention};

	use Suphle\Adapters\Presentation\Hotwire\Formats\BaseHotwireStream;

	use Suphle\Request\RequestDetails;

	class HttpMethodValidationConvention implements ValidationFailureConvention {

		public function __construct (

			protected readonly RequestDetails $requestDetails
		) {

			//
		}

		public function deriveFormPartial (BaseHotwireStream $renderer, array $failureDetails):BaseRenderer {

			$renderer->setRawResponse($failureDetails);

			if ($this->requestDetails->isPostRequest())

				return $renderer->retainCreateNodes();

			return $renderer->retainUpdateNodes();
		}
	}
?>
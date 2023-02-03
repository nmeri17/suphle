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

			if ($this->requestDetails->isPostRequest())

				$renderer->retainCreateNodes();

			else $renderer->retainUpdateNodes();

			return $renderer->setRawResponse($failureDetails); // the above calls indicate the manner nodeResponse should read from rawResponse, thus should be called after them
		}
	}
?>
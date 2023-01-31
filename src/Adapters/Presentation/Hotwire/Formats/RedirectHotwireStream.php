<?php
	namespace Suphle\Adapters\Presentation\Hotwire\Formats;

	use Suphle\Response\Format\Redirect;

	use Suphle\Hydration\Structures\CallbackDetails;

	class RedirectHotwireStream extends BaseHotwireStream {

		public function __construct(

			protected string $handler, callable $destination
		) {

			$this->fallbackRenderer = new Redirect($handler, $destination);

			/**
			 * @see https://turbo.hotwired.dev/handbook/drive#redirecting-after-a-form-submission
			*/
			$this->fallbackRenderer->setHeaders(303, []);
		}

		public function setCallbackDetails (CallbackDetails $callbackDetails):void {

			$this->callbackDetails = $callbackDetails;

			$this->fallbackRenderer->setCallbackDetails($callbackDetails);
		}
	}
?>
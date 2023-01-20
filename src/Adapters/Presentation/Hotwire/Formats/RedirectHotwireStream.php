<?php
	namespace Suphle\Adapters\Presentation\Hotwire\Formats;

	use Suphle\Response\Format\Redirect;

	class RedirectHotwireStream extends BaseHotwireStream {

		public function __construct(string $handler, callable $destination) {

			$this->fallbackRenderer = new Redirect($handler, $destination);

			/**
			 * @see https://turbo.hotwired.dev/handbook/drive#redirecting-after-a-form-submission
			*/
			$this->fallbackRenderer->statusCode = 303;
		}
	}
?>
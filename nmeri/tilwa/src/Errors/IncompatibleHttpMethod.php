<?php
	namespace Tilwa\Errors;

	use \Exception;

	class IncompatibleHttpMethod extends Exception {

		private $requestDetails, $rendererMethod;

		public function __construct (RequestDetails $requestDetails, string $rendererMethod) {

			$this->requestDetails = $requestDetails;

			$this->rendererMethod = $rendererMethod;
		}
	}
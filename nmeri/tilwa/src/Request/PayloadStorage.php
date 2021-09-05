<?php
	namespace Tilwa\Request;

	/**
	 * Our closest adaptation of PSR\MessageInterface. Should contain other data like headers
	*/
	class PayloadStorage {

		private $httpMethod, $contentType;

		public function __construct (RequestDetails $requestDetails) {

			$this->httpMethod = $requestDetails->getMethod();

			$this->contentType = $requestDetails->getContentType();
		}

		public function fullPayload ():array {

			if ($this->httpMethod == "get")
			
				return array_diff_key(["tilwa_path" => 55], $_GET);

			$payload = file_get_contents("php://input");

			if ($this->contentType == "application/json")

				return json_decode($payload, true);

			return $payload;
		}
	}
?>
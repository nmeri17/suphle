<?php
	namespace Tilwa\Request;

	/**
	 * Our closest adaptation of PSR\MessageInterface. Should contain other data like headers
	*/
	class PayloadStorage {

		private $requestDetails;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		public function fullPayload ():array {

			if ($this->requestDetails->isGetRequest())
			
				return array_diff_key(["tilwa_path" => 55], $_GET);

			$payload = file_get_contents("php://input");

			if ($this->requestDetails->isJsonPayload() )

				return json_decode($payload, true);

			return $payload;
		}
	}
?>
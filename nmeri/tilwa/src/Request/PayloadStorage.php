<?php
	namespace Tilwa\Request;

	/**
	 * Our closest adaptation of PSR\MessageInterface
	*/
	class PayloadStorage {

		const JSON_HEADER_VALUE = "application/json";

		private $requestDetails, $headers;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;

			$this->headers = getallheaders();
		}

		public function fullPayload ():array {

			if ($this->requestDetails->isGetRequest())
			
				return array_diff_key(["tilwa_path" => 55], $_GET);

			$payload = file_get_contents("php://input");

			if ($this->isJsonPayload() )

				return json_decode($payload, true);

			return $payload;
		}

		public function isJsonPayload ():bool {

			return strtolower($this->headers["Content-Type"]) == self::JSON_HEADER_VALUE;
		}

		public function acceptsJson():bool {

			return strtolower($this->headers["Accept"]) == self::JSON_HEADER_VALUE;
		}

		public function hasHeader (string $name):bool {

			return array_key_exists($name, $this->headers);
		}

		public function getHeader (string $name):string {

			return $this->headers[$name];
		}

		public function hasKey (string $property):bool {

			return array_key_exists($property, $this->fullPayload());
		}
	}
?>
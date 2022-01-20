<?php
	namespace Tilwa\Request;

	/**
	 * Our closest adaptation of PSR\MessageInterface
	*/
	class PayloadStorage {

		const JSON_HEADER_VALUE = "application/json";

		private $requestDetails, $headers, $payload;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;

			$this->headers = getallheaders();
		}

		public function fullPayload ():array {

			return $this->payload;
		}

		public function mergePayload (array $upserts):void {

			$this->payload = array_merge($this->payload, $upserts);
		}

		public function setPayload ():void {

			if ($this->requestDetails->isGetRequest())
			
				$this->payload = array_diff_key(["tilwa_path" => 55], $_GET);

			if ($this->isJsonPayload() )

				$this->payload = json_decode(file_get_contents("php://input"), true);

			$this->payload = $_POST;
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

			return array_key_exists($property, $this->payload);
		}

		public function getKey (string $property) {

			return $this->payload[$property];
		}

		public function only (array $include):array {

			return array_filter($this->payload, function ($key) use ($include) {

				return array_key_exists($key, $include);
			}, ARRAY_FILTER_USE_KEY);
		}

		public function except (array $exclude):array {

			return array_filter($this->payload, function ($key) use ($include) {

				return !array_key_exists($key, $include);
			}, ARRAY_FILTER_USE_KEY);
		}
	}
?>
<?php
	namespace Tilwa\Request;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Contracts\Requests\StdInputReader;

	/**
	 * Our closest adaptation of PSR\MessageInterface
	*/
	class PayloadStorage {

		const JSON_HEADER_VALUE = "application/json",

		CONTENT_TYPE_KEY = "Content-Type";

		private $requestDetails, $stdInputReader, $headers, $payload = [];

		public function __construct (RequestDetails $requestDetails, StdInputReader $stdInputReader) {

			$this->requestDetails = $requestDetails;

			$this->stdInputReader = $stdInputReader;

			$this->headers = $stdInputReader->getHeaders();

			$this->setPayload();
		}

		public function fullPayload ():array {

			return $this->payload;
		}

		public function mergePayload (array $upserts):void {

			$this->payload = array_merge($this->payload, $upserts);
		}

		public function setPayload ():void {

			if ($this->requestDetails->isGetRequest())

				$this->payload = $_GET; // subject to change since we may be moving away from using this superglobal but still need a way to read query part

			else if ($this->isJsonPayload() )

				$this->payload = $this->stdInputReader->getPayload();

			else $this->payload = $_POST;
		}

		public function isJsonPayload ():bool {

			return $this->hasHeader(self::CONTENT_TYPE_KEY) &&

			$this->getHeader(self::CONTENT_TYPE_KEY) == self::JSON_HEADER_VALUE;
		}

		public function acceptsJson ():bool {

			$acceptsHeader = "Accept";

			return $this->hasHeader($acceptsHeader) && $this->getHeader($acceptsHeader) == self::JSON_HEADER_VALUE;
		}

		public function hasHeader (string $name):bool {

			return array_key_exists($name, $this->headers);
		}

		public function getHeader (string $name):string {

			return strtolower($this->headers[$name]);
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

			return array_filter($this->payload, function ($key) use ($exclude) {

				return !array_key_exists($key, $exclude);
			}, ARRAY_FILTER_USE_KEY);
		}
	}
?>
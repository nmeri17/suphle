<?php
	namespace Suphle\Request;

	use Suphle\Request\RequestDetails;

	use Suphle\Contracts\{Requests\StdInputReader, Services\Decorators\BindsAsSingleton};

	use Suphle\Hydration\Structures\BaseSingletonBind;

	/**
	 * Our closest adaptation of PSR\MessageInterface
	*/
	class PayloadStorage implements BindsAsSingleton {

		use SanitizesIntegerInput, BaseSingletonBind;

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

				$this->payload = $this->requestDetails->getQueryParameters();

			else if ($this->isJsonPayload() )

				$this->payload = $this->stdInputReader->getPayload();

			else $this->payload = $_POST;
		}

		public function isJsonPayload ():bool {

			return $this->hasHeader(self::CONTENT_TYPE_KEY) &&

			$this->matchesHeader(self::CONTENT_TYPE_KEY, self::JSON_HEADER_VALUE);
		}

		public function acceptsJson ():bool {

			$acceptsHeader = "Accept";

			return $this->hasHeader($acceptsHeader) &&

			$this->matchesHeader($acceptsHeader, self::JSON_HEADER_VALUE);
		}

		public function hasHeader (string $name):bool {

			return array_key_exists($name, $this->headers);
		}

		public function getHeader (string $name):string {

			return $this->headers[$name];
		}

		public function matchesHeader (string $name, string $expectedValue):bool {

			$currentValue = str_replace("/", "\/", $this->getHeader($name));

			return preg_match("/^$currentValue$/i", $expectedValue);
		}

		public function hasKey (string $property):bool {

			return array_key_exists($property, $this->payload);
		}

		public function getKey (string $property) {

			return $this->payload[$property];
		}

		/**
		 * Should be called before the readers start calling [getKey]
		*/
		public function allNumericToPositive ():void {

			$this->payload = $this->allInputToPositive($this->payload);
		}

		public function getKeyForPositiveInt (string $key):int {

			return $this->positiveIntValue($this->payload[$key]);
		}

		public function only (array $include):array {

			return array_filter($this->payload, function ($key) use ($include) {

				return array_key_exists($key, $include);
			}, ARRAY_FILTER_USE_KEY);
		}

		public function except (array $exclude):array {

			return array_filter($this->payload, function ($key) use ($exclude) {

				return !in_array($key, $exclude);
			}, ARRAY_FILTER_USE_KEY);
		}
	}
?>
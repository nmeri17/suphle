<?php
	namespace Suphle\Request;

	use Suphle\Contracts\Requests\StdInputReader;

	use Suphle\Services\Decorators\BindsAsSingleton;

	/**
	 * Our closest adaptation of PSR\MessageInterface
	*/
	#[BindsAsSingleton]
	class PayloadStorage {

		use SanitizesIntegerInput;

		final const JSON_HEADER_VALUE = "application/json",

		HTML_HEADER_VALUE = "text/html",
  
  		CONTENT_TYPE_KEY = "Content-Type",

  		ACCEPTS_KEY = "Accept", LOCATION_KEY = "Location";

		protected array $payload = [], $headers;

		public function __construct (

			protected readonly RequestDetails $requestDetails,

			protected readonly StdInputReader $stdInputReader
		) {

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

			return $this->matchesHeader(

				self::CONTENT_TYPE_KEY, self::JSON_HEADER_VALUE
			);
		}

		public function acceptsJson ():bool {

			return $this->matchesHeader(

				self::ACCEPTS_KEY, self::JSON_HEADER_VALUE
			);
		}

		public function hasHeader (string $name):bool {

			return array_key_exists($name, $this->headers);
		}

		public function getHeader (string $name):string {

			return $this->headers[$name];
		}

		public function matchesHeader (string $name, string $expectedValue):bool {

			if (!$this->hasHeader($name)) return false;

			$currentValue = str_replace("/", "\/", $this->getHeader($name));

			return preg_match("/^$currentValue$/i", $expectedValue);
		}

		public function hasKey (string $property):bool {

			return array_key_exists($property, $this->payload);
		}

		public function keyHasContent (string $property):bool {

			return $this->hasKey($property) &&

			!empty($this->getKey($property));
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

			return array_filter($this->fullPayload(), fn($key) => in_array($key, $include), ARRAY_FILTER_USE_KEY);
		}

		public function except (array $exclude):array {

			return array_filter($this->fullPayload(), fn($key) => !in_array($key, $exclude), ARRAY_FILTER_USE_KEY);
		}
	}
?>
<?php
	namespace Suphle\Request;

	use Suphle\Services\Decorators\BindsAsSingleton;

	use GuzzleHttp\Psr7\ServerRequest;

	#[BindsAsSingleton]
	class PayloadStorage extends ServerRequest {

		use SanitizesIntegerInput;

		final const JSON_HEADER_VALUE = "application/json",

		HTML_HEADER_VALUE = "text/html",
  
  		CONTENT_TYPE_KEY = "Content-Type",

  		ACCEPTS_KEY = "Accept", LOCATION_KEY = "Location";

		protected array $payload = [];

		public function __construct (

			protected readonly RequestDetails $requestDetails
		) {

			$this->psrRequest = self::fromGlobals();

			$this->setPayload();
		}

		protected function setPayload ():void {

			if ($this->requestDetails->isGetRequest())

				$this->payload = $this->psrRequest->getQueryParams();

			else $this->payload = $this->psrRequest->getParsedBody();
		}

		public function fullPayload ():array {

			return $this->payload;
		}

		public function mergePayload (array $upserts):void {

			$this->payload = array_merge($this->payload, $upserts);
		}

		public function acceptsJson ():bool {

			return $this->matchesHeader(

				self::ACCEPTS_KEY, self::JSON_HEADER_VALUE
			);
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

		public function matchesContent (string $property, $expectedValue):bool {

			return $this->keyHasContent($property) &&

			$this->getKey($property) == $expectedValue;
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
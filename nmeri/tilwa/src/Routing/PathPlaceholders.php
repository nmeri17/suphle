<?php
	namespace Tilwa\Routing;

	/**
	 * Used by route finder during matching to compose and interpolate patterns read from collections and what is incoming in request
	*/
	class PathPlaceholders {

		private $stack = [], $methodSegments = [], $urlReplacer;

		public function __construct (CollectionMethodToUrl $urlReplacer) {

			$this->urlReplacer = $urlReplacer;
		}

		public function setMethodSegments (array $methods):void {

			$this->methodSegments = $methods;
		}

		/**
		 * Given computed path such as FOO/id, and incoming request with path foo/5, it synchronizes in-app storage of placeholders, recording id as 5
		*/
		public function exchangeTokenValues (string $requestPath):void {

			if (empty($this->stack)) return;

			$realSegments = explode("/", trim($requestPath, "/"));

			foreach ($this->splitMethodSegments() as $index => $segment) {

				if (array_key_exists($segment, $this->stack))

					$this->stack[$segment] = $realSegments[$index];
			}
		}

		public function getPathFromStack (string $urlPattern):string {

			if (empty($this->stack)) return $urlPattern;

			$realSegments = explode("/", trim($urlPattern, "/"));

			foreach ($this->splitMethodSegments() as $index => $segment) {

				if (array_key_exists($segment, $this->stack))

					$realSegments[$index] = $this->stack[$segment];
			}

			return implode("/", $realSegments);
		}

		private function splitMethodSegments ():array {

			$tokenizedUrl = $this->urlReplacer->replacePlaceholders(

				implode("", $this->methodSegments),

				CollectionMethodToUrl::REPLACEMENT_TYPE_PLACEHOLDER
			)
			->regexifiedUrl();

			return $this->urlReplacer->splitIntoSegments($tokenizedUrl);
		}

		public function getSegmentValue (string $name) {

			return $this->stack[$name];
		}

		public function getAllSegmentValues ():array {

			return $this->stack;
		}

		public function overwriteValues (array $newStack):void {

			foreach ($newStack as $index => $value)

				if (array_key_exists($index, $this->stack))

					$this->stack[$index] = $value;
		}

		public function clearAllSegments ():void {

			$this->stack = [];
		}

		public function foundSegments (array $placeholders):void {

			foreach ($placeholders as $key)

				$this->stack[$key] = null;
		}
	}
?>
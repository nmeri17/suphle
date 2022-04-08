<?php
	namespace Tilwa\Routing;

	use Tilwa\Request\RequestDetails;

	/**
	 * Used by route finder during matching to compose and interpolate patterns read from collections and what is incoming in request
	*/
	class PathPlaceholders {

		private $stack = [], $hasExtractedToken = false,

		$methodSegments = [], $urlReplacer, $requestPath;

		public function __construct(RequestDetails $requestDetails, CollectionMethodToUrl $urlReplacer) {

			$this->requestPath = $requestDetails->getPath();

			$this->urlReplacer = $urlReplacer;
		}

		public function setMethodSegments (array $methods):void {

			$this->methodSegments = $methods;
		}

		/**
		 * Given computed path such as FOO/id, and incoming request with path foo/5, it synchronizes in-app storage of placeholders, recording id as 5
		*
		*/
		private function extractTokenValue ():void {

			if ($this->hasExtractedToken) return;

			if (empty($this->stack)) {

				$this->hasExtractedToken = true;

				return;
			}

			$realSegments = explode("/", trim($this->requestPath, "/"));

			foreach ($this->splitMethodSegments() as $index => $segment) {

				if (array_key_exists($segment, $this->stack))

					$this->stack[$segment] = $realSegments[$index];
			}

			$this->hasExtractedToken = true;
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

			$this->extractTokenValue();

			return $this->stack[$name];
		}

		public function getAllSegmentValues ():array {

			$this->extractTokenValue();

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
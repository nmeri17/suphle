<?php
	namespace Tilwa\Routing;

	use Tilwa\Errors\IncompatiblePatternReplacement;

	class PathPlaceholders {

		private $stack = [], $requestPath;

		function __construct( RequestDetails $requestDetails) {

			$this->requestPath = $requestDetails->getPath();
		}

		public function pushSegment (string $name):void {

			$this->stack[$name] = null;
		}

		/**
		 * Given computed path such as FOO/id, and incoming request with path foo/5, it synchronizes in-app storage of placeholders, recording id as 5
		*
		* @throws IncompatiblePatternReplacement
		* @return the synchronized path
		*/
		public function replaceInPattern (string $computed):string {

			$newPattern = [];

			$realSegments = explode("/", rtrim($this->requestPath, "/"));

			$computedSegments = explode("/", rtrim($computed, "/"));

			if (count($realSegments) != count($computedSegments))

				throw new IncompatiblePatternReplacement;
				;
//var_dump($computedSegments, $realSegments);
			foreach ($computedSegments as $index => $value) {

				if (!empty($value)) {

					$segmentValue = $realSegments[$index];

					$segmentIsPlaceholder = strtolower($value) === $value;

					$differentSegments = !preg_match("/^" . $segmentValue . "$/i", $value);

					if ($differentSegments) {

						if ($segmentIsPlaceholder)

							$newPattern[] = $this->stack[$value] = $segmentValue;

						else throw new IncompatiblePatternReplacement;
					}

					else $newPattern[] = $value;
				}
			}

			return "/" . implode("/", $newPattern);
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
	}
?>
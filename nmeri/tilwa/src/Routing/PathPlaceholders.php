<?php
	namespace Tilwa\Routing;

	class PathPlaceholders {

		private $fixed = [], $optional = [], $requestDetails;

		function __construct( RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		public function addFixed (string $name, string $value):void {

			$this->fixed[$name] = $value;
		}

		public function addOptional (string $name, string $value):void {

			$this->optional[$name] = $value;
		}

		public function getFixed ():array {

			return $this->fixed;
		}

		public function getOptional ():array {

			return $this->optional;
		}

		public function replaceInPattern (string $computed) {

			// pattern = /static/id. our guy already tells us each segment that's placeholder or static. so just split
		}
	}
?>
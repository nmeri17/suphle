<?php
	namespace Tilwa\Routing;

	class PathPlaceholders {

		private $fixed = [], $optional = [];

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
	}
?>
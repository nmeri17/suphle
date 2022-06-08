<?php
	namespace Tilwa\Routing\Structures;

	class MethodPlaceholders {

		private $regexified, $placeholders;

		public function __construct (string $regexified, array $placeholders ) {

			$this->regexified = $regexified;

			$this->placeholders = $placeholders;
		}

		public function getPlaceholders ():array {

			return $this->placeholders;
		}

		public function regexifiedUrl ():string {

			return $this->regexified;
		}
	}
?>
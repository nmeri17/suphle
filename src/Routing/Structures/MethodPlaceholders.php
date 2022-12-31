<?php
	namespace Suphle\Routing\Structures;

	class MethodPlaceholders {

		public function __construct(protected readonly string $regexified, protected readonly array $placeholders) {

			//
		}

		public function getPlaceholders ():array {

			return $this->placeholders;
		}

		public function regexifiedUrl ():string {

			return $this->regexified;
		}
	}
?>
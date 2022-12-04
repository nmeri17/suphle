<?php
	namespace Suphle\Routing\Structures;

	class MethodPlaceholders {

		public function __construct(private readonly string $regexified, private readonly array $placeholders)
  {
  }

		public function getPlaceholders ():array {

			return $this->placeholders;
		}

		public function regexifiedUrl ():string {

			return $this->regexified;
		}
	}
?>
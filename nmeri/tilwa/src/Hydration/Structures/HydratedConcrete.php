<?php
	namespace Tilwa\Hydration\Structures;

	class HydratedConcrete {

		private $concrete, $createdFor;

		public function __construct ($concrete, string $createdFor) {

			$this->createdFor = $createdFor;

			$this->concrete = $concrete;
		}

		public function getCreatedFor ():string {

			return $this->createdFor;
		}

		public function getConcrete () {

			return $this->concrete;
		}
	}
?>
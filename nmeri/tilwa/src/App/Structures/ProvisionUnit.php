<?php

	namespace Tilwa\App\Structures;

	/**
	*	Blueprint for each provided entity
	*/
	class ProvisionUnit {

		private $concretes = [], // populated by `needs`

		$arguments = [];

		public function hasConcrete(string $fullName):bool {
			
			return array_key_exists($fullName, $this->concretes);
		}

		public function getConcrete(string $fullName) {
			
			return $this->concretes[$fullName];
		}

		public function addConcrete(string $fullName, object $instance):self {
			
			$this->concretes[$fullName] = $instance;

			return $this;
		}

		public function updateConcretes(array $dependencyList):self {
			
			$this->concretes += $dependencyList;
		}

		public function updateArguments(array $parameters):self {
			
			$this->arguments += $parameters;
		}

		public function hasArgument(string $parameter):bool {
			
			return array_key_exists($parameter, $this->arguments);
		}

		public function getArgument(string $fullName) {
			
			return $this->arguments[$fullName];
		}
	}
?>
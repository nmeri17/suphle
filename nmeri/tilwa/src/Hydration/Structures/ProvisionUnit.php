<?php
	namespace Tilwa\Hydration\Structures;

	/**
	*	Blueprint for each provided entity
	*/
	class ProvisionUnit {

		private $owner, $concretes = [], // populated by `needs`

		$arguments = [];

		public function __construct (string $owner) {

			$this->owner = $owner;
		}

		public function getOwner ():string {

			return $this->owner;
		}

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

		public function updateConcretes(array $dependencyList):void {
			
			$this->concretes = array_merge($this->concretes, $dependencyList);
		}

		public function updateArguments(array $parameters):void {
			
			$this->arguments = array_merge($this->arguments, $parameters);
		}

		/**
		 * @param {parameter} Can either be parameter name or type
		*/
		public function hasArgument(string $parameter):bool {
			
			return array_key_exists($parameter, $this->arguments);
		}

		public function getArgument(string $fullName) {
			
			return $this->arguments[$fullName];
		}

		public function clearInProvision (string $fullName):void {

			unset($this->arguments[$fullName]);

			unset($this->concretes[$fullName]);
		}

		public function hasAnywhere (string $fullName):bool {

			return $this->hasConcrete($fullName) || $this->hasArgument($fullName);
		}
	}
?>
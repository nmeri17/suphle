<?php
	namespace Tilwa\Controllers\Structures;

	class UseCase {

		private $condition, $arguments;

		public function __construct (callable $condition, array $arguments) {

			$this->condition = $condition;

			$this->arguments = $arguments;
		}

		public function build ():bool {

			return call_user_func($this->condition);
		}

		public function getArguments ():array {

			return $this->arguments;
		} 
	}
?>
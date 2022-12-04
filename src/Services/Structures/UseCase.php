<?php
	namespace Suphle\Services\Structures;

	class UseCase {

		private $condition;

		public function __construct (callable $condition, private readonly array $arguments) {

			$this->condition = $condition;
		}

		public function build ():bool {

			return call_user_func_array($this->condition, $this->arguments);
		}

		public function getArguments ():array {

			return $this->arguments;
		} 
	}
?>
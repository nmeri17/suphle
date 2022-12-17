<?php
	namespace Suphle\Services\Structures;

	use Closure;

	class UseCase {

		public function __construct (

			private readonly Closure $condition,

			private readonly array $arguments
		) {

			//
		}

		public function build ():bool {

			return call_user_func_array($this->condition, $this->arguments);
		}

		public function getArguments ():array {

			return $this->arguments;
		} 
	}
?>
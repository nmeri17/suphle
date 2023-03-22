<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\StaticChecks;

	class UsesNonMatchingTypes {
		/**
		 * @return array<string>
		 */
		public function takesAnInt (int $i) {
			
			return [$i, "hello"];
		}

		public function randomConditional ():void {

			$condition = rand(0, 5);
			if ($condition) {
			} elseif ($condition) {}
		}

		public function callTakesInt () {

			$data = ["some text", 5];

			$this->takesAnInt($data[0]);
		}
	}
?>
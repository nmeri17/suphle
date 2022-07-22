<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class CircularConstructor2 {

		private $dependency, $count;

		public function __construct (CircularConstructor1 $dependency, int $count) {

			$this->dependency = $dependency;

			$this->count = $count;
		}

		public function getCount ():int {

			return $this->count;
		}
	}
?>
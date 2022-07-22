<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class CircularConstructor1 {

		private $dependency;

		public function __construct (CircularConstructor2 $dependency) {

			$this->dependency = $dependency;
		}

		public function getDependencyValue ():int {

			return $this->dependency->getCount();
		}
	}
?>
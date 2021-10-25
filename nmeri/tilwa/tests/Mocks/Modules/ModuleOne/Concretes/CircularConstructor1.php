<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	class CircularConstructor1 {

		private $dependency;

		public function __construct (CircularConstructor2 $dependency) {

			$this->dependency = $dependency;
		}

		public function getDependencyValue ():string {

			return $this->dependency->getCount();
		}
	}
?>
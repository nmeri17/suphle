<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class CircularConstructor1 {

		public function __construct(private readonly CircularConstructor2 $dependency)
  {
  }

		public function getDependencyValue ():int {

			return $this->dependency->getCount();
		}
	}
?>
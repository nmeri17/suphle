<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class CircularConstructor2 {

		public function __construct(private readonly CircularConstructor1 $dependency, private readonly int $count)
  {
  }

		public function getCount ():int {

			return $this->count;
		}
	}
?>
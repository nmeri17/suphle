<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class CircularConstructor2 {

		public function __construct(protected readonly CircularConstructor1 $dependency, protected readonly int $count) {

			//
		}

		public function getCount ():int {

			return $this->count;
		}
	}
?>
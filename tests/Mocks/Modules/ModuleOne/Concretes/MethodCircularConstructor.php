<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class MethodCircularConstructor {

		private $dependency;

		public function __construct (MethodCircularContainer $dependency) {

			$this->dependency = $dependency;
		}
	}
?>
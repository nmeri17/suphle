<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class MethodCircularConstructor {

		public function __construct(private readonly MethodCircularContainer $dependency)
  {
  }
	}
?>
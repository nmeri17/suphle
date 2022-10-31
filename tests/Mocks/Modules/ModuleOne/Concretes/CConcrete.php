<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

	class CConcrete implements CInterface {

		public function __construct(private readonly int $value)
  {
  }

		public function getValue ():int {

			return $this->value;
		}
	}
?>
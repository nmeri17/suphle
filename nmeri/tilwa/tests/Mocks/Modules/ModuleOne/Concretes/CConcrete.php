<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

	class CConcrete implements CInterface {

		private $value;

		public function __construct (int $value) {

			$this->value = $value;
		}

		public function getValue ():int {

			return $this->value;
		}
	}
?>
<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\RewriteSpace;

	class NeedsSpace {

		private $contract;

		public function __construct (RewriteSpace $contract) {

			$this->contract = $contract;
		}

		public function getConcreteValue():int {

			return $this->contract->getValue();
		}
	}
?>
<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Interfaces\RewriteSpace;

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
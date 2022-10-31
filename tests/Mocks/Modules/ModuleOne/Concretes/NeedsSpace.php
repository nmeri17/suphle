<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Interfaces\RewriteSpace;

	class NeedsSpace {

		public function __construct(private readonly RewriteSpace $contract)
  {
  }

		public function getConcreteValue():int {

			return $this->contract->getValue();
		}
	}
?>
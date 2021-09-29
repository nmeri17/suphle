<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\V2;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\RewriteSpace;

	class RewriteSpaceImpl implements RewriteSpace {

		public function getValue ():int {

			return 15;
		}
	}
?>
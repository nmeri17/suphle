<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Tilwa\Contracts\Routing\CanaryGateway;

	class CanaryForUser5 implements CanaryGateway {

		public function willLoad ():bool {

			return;
		}

		public function entryClass ():string {

			return ::class;
		}
	}
?>
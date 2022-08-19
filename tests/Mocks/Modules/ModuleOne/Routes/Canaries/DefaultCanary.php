<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Suphle\Contracts\Routing\CanaryGateway;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\DefaultCollection;

	class DefaultCanary implements CanaryGateway {

		public function willLoad ():bool {

			return true;
		}

		public function entryClass ():string {

			return DefaultCollection::class;
		}
	}
?>
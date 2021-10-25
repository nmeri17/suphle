<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Tilwa\Contracts\Routing\CanaryGateway;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\CollectionRequestHasFoo;

	class CanaryRequestHasFoo implements CanaryGateway {

		private $payloadStorage;

		public function __construct (PayloadStorage $payloadStorage) {

			$this->payloadStorage = $payloadStorage;
		}

		public function willLoad ():bool {

			return $this->payloadStorage->hasKey("foo");
		}

		public function entryClass ():string {

			return CollectionRequestHasFoo::class;
		}
	}
?>
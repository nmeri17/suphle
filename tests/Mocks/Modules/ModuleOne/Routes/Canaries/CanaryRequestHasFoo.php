<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Suphle\Contracts\Routing\CanaryGateway;

	use Suphle\Request\PayloadStorage;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\CollectionRequestHasFoo;

	class CanaryRequestHasFoo implements CanaryGateway {

		public function __construct(private readonly PayloadStorage $payloadStorage)
  {
  }

		public function willLoad ():bool {

			return $this->payloadStorage->hasKey("foo");
		}

		public function entryClass ():string {

			return CollectionRequestHasFoo::class;
		}
	}
?>
<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Request\PayloadStorage;

	class ReadsPayloadController extends ServiceCoordinator {

		private $payloadStorage;

		public function __construct (PayloadStorage $payloadStorage) {

			$this->payloadStorage = $payloadStorage;
		}

		public function mirrorPayload () {

			return $this->payloadStorage->fullPayload();
		}
	}
?>
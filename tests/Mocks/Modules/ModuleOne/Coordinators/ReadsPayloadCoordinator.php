<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

	class ReadsPayloadCoordinator extends ServiceCoordinator {

		private $payloadStorage;

		public function __construct (PayloadStorage $payloadStorage) {

			$this->payloadStorage = $payloadStorage;
		}

		public function mirrorPayload () {

			return $this->payloadStorage->fullPayload();
		}
	}
?>
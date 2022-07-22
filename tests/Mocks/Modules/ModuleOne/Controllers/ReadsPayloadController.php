<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

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
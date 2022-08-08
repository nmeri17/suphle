<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Controllers;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleThree\PayloadReaders\ReadsId;

	class BaseController extends ServiceCoordinator {

		public function checkPlaceholder (ReadsId $payloadReader):array {

			return [

				"id" => $payloadReader->getDomainObject()
			];
		}
	}
?>
<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Controllers;

	use Suphle\Services\ServiceCoordinator;

	class BaseController extends ServiceCoordinator {

		public function __construct () {
		}

		public function checkPlaceholder ():array {

			return [

				//"id" => $this->pathPlaceholders->getSegmentValue("id") // in real life, this will be read and handled by the modelfulPayload
			];
		}
	}
?>
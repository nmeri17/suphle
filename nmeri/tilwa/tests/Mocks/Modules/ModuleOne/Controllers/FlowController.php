<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	class FlowController extends ServiceCoordinator {

		public function noFlowHandler () {}

		public function getPostDetails () {

			return [];
		}

		public function preloaded () {}

		public function parentFlow () {}

		public function handleChildFlow () {}
	}
?>
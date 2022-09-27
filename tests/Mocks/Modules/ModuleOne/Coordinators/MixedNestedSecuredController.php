<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	class MixedNestedSecuredController extends ServiceCoordinator {

		public function handleUnlinked() {

			return [];
		}

		public function handleRetained () {

			return [];
		}
	}
?>
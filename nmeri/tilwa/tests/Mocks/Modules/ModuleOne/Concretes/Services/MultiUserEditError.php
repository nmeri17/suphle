<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	class MultiUserEditError extends MultiUserEditMock {

		public function updateResource () {

			trigger_error("nonsensical", E_USER_ERROR);
		}

		public function failureState (string $method) {

			if ($method == "updateResource")

				return "boo!";
		}
	}
?>
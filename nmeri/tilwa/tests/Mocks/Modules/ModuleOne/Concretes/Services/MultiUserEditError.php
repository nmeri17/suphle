<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Services\Structures\OptionalDTO;

	class MultiUserEditError extends MultiUserEditMock {

		public function updateResource () {

			trigger_error("nonsensical", E_USER_ERROR);
		}

		public function failureState (string $method):?OptionalDTO {

			if ($method == "updateResource")

				return new OptionalDTO("boo!", false);
		}
	}
?>
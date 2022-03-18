<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\{Decorators\MultiUserModelEdit, Models\IntegrityModel};

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Tests\Models\Eloquent\MultiEditProduct;

	class MultiUserEditMock implements MultiUserModelEdit {

		private $integrity;

		public function getResource ():IntegrityModel {

			return new MultiEditProduct(["id" => 55]); // irl, this comes from payloadStorage
		}

		public function updateResource () {

			//
		}

		public function rethrowAs ():array {

			return [];
		}

		public function failureState (string $method):?OptionalDTO {

			return null;
		}
	}
?>
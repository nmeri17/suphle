<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\{Decorators\MultiUserModelEdit, Models\IntegrityModel};

	use Tilwa\Tests\Models\Eloquent\MultiEditProduct;

	class MultiUserEditMock implements MultiUserModelEdit {

		private $integrity;

		public function getResource ():IntegrityModel {

			return new MultiEditProduct(["id" => 55]); // irl, this comes from payloadStorage
		}

		public function setLastIntegrity (int $integrity):void {

			$this->integrity = $integrity;
		}

		public function getLastIntegrity ():int {

			return $this->integrity;
		}

		public function updateResource () {

			//
		}
	}
?>
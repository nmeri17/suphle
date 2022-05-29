<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\{Decorators\MultiUserModelEdit, Models\IntegrityModel};

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Tests\Mocks\Models\Eloquent\MultiEditProduct;

	class MultiUserEditMock implements MultiUserModelEdit {

		private $integrity, $payloadStorage, $model;

		public function __construct (PayloadStorage $payloadStorage, MultiEditProduct $model) {

			$this->payloadStorage = $payloadStorage;

			$this->model = $model;
		}

		public function getResource ():IntegrityModel {

			return $this->model->find($this->payloadStorage->getKey("id"));
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
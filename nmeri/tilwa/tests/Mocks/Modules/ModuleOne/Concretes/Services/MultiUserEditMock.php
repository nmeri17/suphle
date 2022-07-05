<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\{Decorators\MultiUserModelEdit, Models\IntegrityModel};

	use Tilwa\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Tilwa\Routing\PathPlaceholders;

	use Tilwa\Tests\Mocks\Models\Eloquent\MultiEditProduct;

	class MultiUserEditMock extends UpdatefulService implements MultiUserModelEdit {

		use BaseErrorCatcherService;

		private $integrity, $placeholderStorage, $model;

		public function __construct (PathPlaceholders $placeholderStorage, MultiEditProduct $model) {

			$this->placeholderStorage = $placeholderStorage;

			$this->model = $model;
		}

		public function getResource ():IntegrityModel {

			return $this->model->find($this->placeholderStorage->getSegmentValue("id"));
		}

		public function updateResource () {

			//
		}
	}
?>
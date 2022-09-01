<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\{Decorators\MultiUserModelEdit, Models\IntegrityModel};

	use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EmploymentEditMock extends UpdatefulService implements MultiUserModelEdit {

		use BaseErrorCatcherService;

		private $integrity, $placeholderStorage, $model;

		public function __construct (PathPlaceholders $placeholderStorage, Employment $model) {

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
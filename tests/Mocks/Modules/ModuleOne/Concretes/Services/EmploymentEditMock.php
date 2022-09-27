<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\{Decorators\MultiUserModelEdit, Models\IntegrityModel};

	use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Request\PayloadStorage;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EmploymentEditMock extends UpdatefulService implements MultiUserModelEdit {

		use BaseErrorCatcherService;

		private $payloadStorage, $placeholderStorage, $blankModel;

		public function __construct (PathPlaceholders $placeholderStorage, PayloadStorage $payloadStorage, Employment $blankModel) {

			$this->placeholderStorage = $placeholderStorage;

			$this->payloadStorage = $payloadStorage;

			$this->blankModel = $blankModel;
		}

		public function getResource ():IntegrityModel {

			return $this->blankModel->find(

				$this->placeholderStorage->getSegmentValue("id")
			);
		}

		public function updateResource () {

			$this->model->where([

				"id" => $this->payloadStorage->getKey("id")
			])
			->update($this->payloadStorage->only(["salary"]));
		}
	}
?>
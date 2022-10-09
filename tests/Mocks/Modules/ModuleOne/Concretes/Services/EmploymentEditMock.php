<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\Decorators\{MultiUserModelEdit, VariableDependencies};

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EmploymentEditMock extends UpdatefulService implements MultiUserModelEdit, VariableDependencies {

		use BaseErrorCatcherService;

		private $blankModel;

		public function __construct ( Employment $blankModel) {

			$this->blankModel = $blankModel;
		}

		public function getResource ():IntegrityModel {

			return $this->blankModel->find(

				$this->placeholderStorage->getSegmentValue("id")
			);
		}

		public function updateResource () {

			$this->blankModel->where([

				"id" => $this->placeholderStorage->getSegmentValue("id")
			])
			->update($this->payloadStorage->only(["salary"]));
		}
	}
?>
<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\Decorators\{MultiUserModelEdit, VariableDependencies};

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EmploymentEditMock extends UpdatefulService implements MultiUserModelEdit, VariableDependencies {

		use BaseErrorCatcherService;

		public function __construct(private readonly Employment $blankModel)
  {
  }

		public function getResource ():IntegrityModel {

			return $this->blankModel->find(

				$this->pathPlaceholders->getSegmentValue("id")
			);
		}

		public function updateResource () {

			return $this->blankModel->where([

				"id" => $this->pathPlaceholders->getSegmentValue("id")
			])
			->update($this->payloadStorage->only(["salary"]));
		}
	}
?>
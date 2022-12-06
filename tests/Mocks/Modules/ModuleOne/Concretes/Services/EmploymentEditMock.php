<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\CallInterceptors\MultiUserModelEdit;

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	#[InterceptsCalls(MultiUserModelEdit::class)]
	#[VariableDependencies([

		"setPayloadStorage", "setPlaceholderStorage"
	])]
	class EmploymentEditMock extends UpdatefulService implements MultiUserModelEdit {

		use BaseErrorCatcherService;

		public function __construct(private readonly Employment $blankModel) {

			//
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
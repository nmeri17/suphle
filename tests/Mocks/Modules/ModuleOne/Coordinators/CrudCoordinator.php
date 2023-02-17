<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

	use Suphle\Request\PayloadStorage;

	use Suphle\Routing\Crud\BrowserBuilder;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\SystemModelEditMock1;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class CrudCoordinator extends ServiceCoordinator {

		public function __construct(
			protected readonly SystemModelEditMock1 $editService,

			protected readonly PayloadStorage $payloadStorage
		) {

			//
		}

		public function showCreateForm() {

			return [];
		}

		#[ValidationRules(["title" => "required"])]
		public function saveNew() {

			$blankModel = new Employment;

			return [

				BrowserBuilder::SAVE_NEW_KEY => $blankModel->create(

					$this->payloadStorage->only([
					
						"title", "employer_id", "salary"
					])
				)
			];
		}

		public function showAll() {

			return [];
		}

		public function showOne() {

			return [];
		}

		#[ValidationRules(["id" => "required"])]
		public function updateOne() {

			return [];
		}

		#[ValidationRules(["id" => "required"])]
		public function deleteOne() {

			return [];
		}

		public function showSearchForm () {

			return [];
		}

		public function myOverride () {

			return [];
		}

		public function showEditForm () {

			return [];
		}
	}
?>
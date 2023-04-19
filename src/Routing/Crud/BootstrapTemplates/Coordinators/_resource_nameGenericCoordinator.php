<?php
	namespace AllModules\_module_name\Coordinators;

	use Suphle\Services\Decorators\ValidationRules;

	use Suphle\Routing\Crud\BrowserBuilder;

	use _database_namespace\_resource_name;

	use AllModules\_module_name\PayloadReaders\Base_resource_nameBuilder;

	trait _resource_nameGenericCoordinator {

		#[ValidationRules(["title" => "required"])]
		public function saveNew():iterable {

			$blankModel = new _resource_name;

			return [

				BrowserBuilder::SAVE_NEW_KEY => $blankModel->create(

					$this->payloadStorage->only(["title"])
				)
			];
		}

		public function showAll():iterable {

			return [];
		}

		public function showOne (Base_resource_nameBuilder $_resource_nameBuilder):iterable {

			return [];
		}

		#[ValidationRules(["id" => "required"])]
		public function updateOne (Base_resource_nameBuilder $_resource_nameBuilder):iterable {

			return [];
		}

		#[ValidationRules(["id" => "required"])]
		public function deleteOne():iterable {

			return [];
		}
	}
?>
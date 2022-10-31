<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\SystemModelEditMock1;

	class CrudController extends ServiceCoordinator {

		public function __construct(private readonly SystemModelEditMock1 $editService)
  {
  }

		public function showCreateForm() {

			return [];
		}

		public function saveNew() {

			return [];
		}

		public function showAll() {

			return [];
		}

		public function showOne() {

			return [];
		}

		public function updateOne() {

			return [];
		}

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
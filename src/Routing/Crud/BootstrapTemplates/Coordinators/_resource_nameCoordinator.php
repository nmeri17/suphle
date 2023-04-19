<?php
	namespace AllModules\_module_name\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

	use AllModules\_module_name\PayloadReaders\Base_resource_nameBuilder;

	class _resource_nameCoordinator extends ServiceCoordinator {

		use _resource_nameGenericCoordinator;

		public function __construct(protected readonly PayloadStorage $payloadStorage) {

			//
		}

		public function showCreateForm ():iterable {

			return [];
		}

		public function showSearchForm ():iterable {

			return [];
		}

		public function showEditForm (Base_resource_nameBuilder $_resource_nameBuilder):iterable {

			return [];
		}
	}
?>
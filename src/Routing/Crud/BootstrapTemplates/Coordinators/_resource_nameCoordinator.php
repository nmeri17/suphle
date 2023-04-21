<?php
	namespace _modules_shell\_module_name\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

	use Suphle\Security\CSRF\CsrfGenerator;

	use _modules_shell\_module_name\PayloadReaders\Base_resource_nameBuilder;

	class _resource_nameCoordinator extends ServiceCoordinator {

		use _resource_nameGenericCoordinator;

		public function __construct (

			protected readonly PayloadStorage $payloadStorage,

			protected readonly CsrfGenerator $csrf
		) {

			//
		}

		public function showCreateForm ():iterable {

			return [

				CsrfGenerator::TOKEN_FIELD => $this->csrf->newToken()
			];
		}

		public function showSearchForm ():iterable {

			return [];
		}

		public function showEditForm (Base_resource_nameBuilder $_resource_nameBuilder):iterable {

			return [];
		}
	}
?>
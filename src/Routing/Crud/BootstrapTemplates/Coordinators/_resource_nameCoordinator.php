<?php
	namespace _modules_shell\_module_name\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Contracts\IO\Session;

	use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

	use _modules_shell\_module_name\PayloadReaders\Base_resource_nameBuilder;

	class _resource_nameCoordinator extends ServiceCoordinator {

		use _resource_nameGenericCoordinator;

		public function __construct (

			protected readonly PayloadStorage $payloadStorage,

			protected readonly CsrfGenerator $csrf,

			protected readonly Session $sessionClient
		) {

			//
		}

		public function showCreateForm ():iterable {

			return $this->copyValidationErrors([

				CsrfGenerator::TOKEN_FIELD => $this->csrf->newToken()
			]);
		}

		protected function copyValidationErrors (array $payload):array {

			if ($this->sessionClient->hasOldInput(ValidationFailureDiffuser::ERRORS_PRESENCE)) {

				foreach (ValidationFailureDiffuser::FAILURE_KEYS as $key)

					$payload[$key] = $this->sessionClient->getOldInput($key);
			}

			return $payload;
		}

		public function showSearchForm ():iterable {

			return [];
		}

		public function showEditForm (Base_resource_nameBuilder $_resource_nameBuilder):iterable {

			return [];
		}
	}
?>
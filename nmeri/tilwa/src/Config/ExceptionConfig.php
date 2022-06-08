<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\{ExceptionInterceptor, ModuleFiles};

	use Tilwa\Exception\Explosives\{NotFoundException, Unauthenticated, ValidationFailure, UnauthorizedServiceAccess, EditIntegrityException};

	use Tilwa\Exception\Diffusers\{GenericDiffuser, NotFoundDiffuser, ValidationFailureDiffuser, UnauthorizedDiffuser, UnauthenticatedDiffuser, StaleEditDiffuser};

	class ExceptionConfig implements ExceptionInterceptor {

		private $fileConfig;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;
		}

		public function getHandlers ():array {

			return [
				NotFoundException::class => NotFoundDiffuser::class,

				Unauthenticated::class => UnauthenticatedDiffuser::class,

				ValidationFailure::class => ValidationFailureDiffuser::class,

				UnauthorizedServiceAccess::class => UnauthorizedDiffuser::class,

				EditIntegrityException::class => StaleEditDiffuser::class
			];
		}

		public function defaultHandler ():string {

			return GenericDiffuser::class;
		}

		public function shutdownLog ():string {

			return $this->fileConfig->activeModulePath() . "shutdown-log.txt";
		}

		public function shutdownText ():string {

			return "Unable to handle request right now";
		}
	}
?>
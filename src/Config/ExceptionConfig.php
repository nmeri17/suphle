<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\{ExceptionInterceptor, ModuleFiles};

	use Suphle\Exception\Explosives\{NotFoundException, Unauthenticated, ValidationFailure, UnauthorizedServiceAccess, EditIntegrityException};

	use Suphle\Exception\Diffusers\{GenericDiffuser, NotFoundDiffuser, ValidationFailureDiffuser, UnauthorizedDiffuser, UnauthenticatedDiffuser, StaleEditDiffuser};

	class ExceptionConfig implements ExceptionInterceptor {

		public function __construct(private readonly ModuleFiles $fileConfig)
  {
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

		/**
		 * {@inheritdoc}
		*/
		public function shutdownText ():string {

			return "Unable to handle this request :( But not to worry; our engineers are on top of the situation";
		}
	}
?>
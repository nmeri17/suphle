<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\ExceptionInterceptor;

	use Tilwa\Exception\Explosives\{NotFoundException, Unauthenticated, ValidationFailure, UnauthorizedServiceAccess};

	use Tilwa\Exception\Diffusers\{GenericDiffuser, NotFoundDiffuser, ValidationFailureDiffuser, UnauthorizedDiffuser, UnauthenticatedDiffuser};

	class ExceptionConfig implements ExceptionInterceptor {

		public function getHandlers ():array {

			return [
				NotFoundException::class => NotFoundDiffuser::class,

				Unauthenticated::class => UnauthenticatedDiffuser::class,

				ValidationFailure::class => ValidationFailureDiffuser::class,

				UnauthorizedServiceAccess::class => UnauthorizedDiffuser::class
			];
		}

		public function defaultHandler ():string {

			return GenericDiffuser::class;
		}
	}
?>
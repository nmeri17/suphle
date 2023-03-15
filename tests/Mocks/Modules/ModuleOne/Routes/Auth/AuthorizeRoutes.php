<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry, Decorators\HandlingCoordinator};

	use Suphle\Auth\RequestScrutinizers\PathAuthorizationScrutinizer;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\BaseCoordinator, Authorization\Paths\AdminRule};

	#[HandlingCoordinator(BaseCoordinator::class)]
	class AuthorizeRoutes extends BaseCollection {

		public function ADMIN__ENTRYh () {

			$this->_get(new Json("plainSegment"));
		}

		public function ADMIN () {

			$this->_prefixFor(UnlocksAuthorization1::class);
		}

		public function _preMiddleware (PreMiddlewareRegistry $registry):void {

			$registry->tagPatterns(

				new PathAuthorizationScrutinizer(["ADMIN__ENTRYh", "ADMIN"], AdminRule::class)
			);
		}
	}
?>
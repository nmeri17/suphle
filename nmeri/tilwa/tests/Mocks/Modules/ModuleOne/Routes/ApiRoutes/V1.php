<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes;

	use Tilwa\Routing\BaseCollection;

	class V1 extends BaseCollection {

		public function __construct(CanaryValidator $validator, RouterConfig $routerConfig, TokenStorage $tokenStorage, MiddlewareRegistry $middlewareRegistry) {

			parent::__construct($validator, $routerConfig, $tokenStorage, $middlewareRegistry);
		}
		
		public function _index() {
			
			return $this->_mirrorBrowserRoutes($this->authStorage);
		}
	}
?>
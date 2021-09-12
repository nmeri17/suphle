<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Routing\{BaseCollection, CanaryValidator};

	use Tilwa\Contracts\Config\Router as RouterConfig;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	use Tilwa\Auth\SessionStorage;

	use Tilwa\Middleware\MiddlewareRegistry;

	class BrowserNoPrefix extends BaseCollection {

		/*function __construct(CanaryValidator $validator, RouterConfig $routerConfig, SessionStorage $authStorage, MiddlewareRegistry $middlewareRegistry) {

			$this->routerConfig = $routerConfig;

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;

			$this->middlewareRegistry = $middlewareRegistry;
		}*/

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function SEGMENT() {

			return $this->_get(new Json("plainSegment"));
		}

		public function SEGMENT_id() {

			return $this->_get(new Json("simplePair"));
		}

		public function SEGMENT__SEGMENTh_id() {

			return $this->_get(new Json("hyphenatedSegments"));
		}

		public function SEGMENT__SEGMENTu_id() {

			return $this->_get(new Json("underscoredSegments"));
		}

		public function SEGMENT_id_SEGMENT_idO() {

			return $this->_get(new Json("optionalPlaceholder"));
		}
	}
?>
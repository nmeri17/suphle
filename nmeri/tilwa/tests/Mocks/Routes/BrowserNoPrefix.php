<?php

	namespace Tilwa\Tests\Mocks\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Contracts\Config\Router as RouterConfig;

	use Tilwa\Tests\Mocks\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class BrowserNoPrefix extends BaseCollection {

		function __construct(CanaryValidator $validator, RouterConfig $routerConfig, SessionStorage $authStorage, MiddlewareRegistry $middlewareRegistry) {

			$this->routerConfig = $routerConfig;

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;

			$this->middlewareRegistry = $middlewareRegistry;
		}

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

		public function SEGMENT_SEGMENTh_id() {

			return $this->_get(new Json("underscoredSegments"));
		}

		public function SEGMENT_id_SEGMENT_id0() {

			return $this->_get(new Json("optionalPlaceholder"));
		}
	}
?>
<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use Suphle\Response\Format\Json;

	#[HandlingCoordinator(BaseCoordinator::class)]
	class BrowserNoPrefix extends BaseCollection {

		public function SEGMENT() {

			$this->_httpGet(new Json("plainSegment"));
		}

		public function SEGMENT_id() {

			$this->_httpGet(new Json("simplePair"));
		}

		public function SEGMENT__SEGMENTh_id() {

			$this->_httpGet(new Json("hyphenatedSegments"));
		}

		public function SEGMENT__SEGMENTu_id() {

			$this->_httpGet(new Json("underscoredSegments"));
		}

		public function SEGMENT_id_SEGMENT_id2() {

			$this->_httpGet(new Json("optionalPlaceholder"));
		}

		public function _index () {

			$this->_httpGet(new Json("indexHandler"));
		}
	}
?>
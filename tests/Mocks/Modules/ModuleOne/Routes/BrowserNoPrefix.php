<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use Suphle\Response\Format\Json;

	class BrowserNoPrefix extends BaseCollection {

		public function _handlingClass ():string {

			return BaseCoordinator::class;
		}

		public function SEGMENT() {

			$this->_get(new Json("plainSegment"));
		}

		public function SEGMENT_id() {

			$this->_get(new Json("simplePair"));
		}

		public function SEGMENT__SEGMENTh_id() {

			$this->_get(new Json("hyphenatedSegments"));
		}

		public function SEGMENT__SEGMENTu_id() {

			$this->_get(new Json("underscoredSegments"));
		}

		public function SEGMENT_id_SEGMENT_id2() {

			$this->_get(new Json("optionalPlaceholder"));
		}

		public function _index () {

			$this->_get(new Json("indexHandler"));
		}
	}
?>
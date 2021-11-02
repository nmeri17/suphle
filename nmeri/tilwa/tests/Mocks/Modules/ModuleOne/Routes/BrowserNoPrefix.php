<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class BrowserNoPrefix extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
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

		public function SEGMENT_id_SEGMENT_idO() {

			$this->_get(new Json("optionalPlaceholder"));
		}

		public function _index () {

			$this->_get(new Json("indexHandler"));
		}
	}
?>
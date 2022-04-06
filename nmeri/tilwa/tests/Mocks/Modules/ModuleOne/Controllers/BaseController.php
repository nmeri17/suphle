<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	class BaseController extends ServiceCoordinator {

		public function indexHandler () {

			//
		}

		public function plainSegment () {

			return ["message" => "plain Segment"];
		}

		public function simplePair () {

			//
		}

		public function hyphenatedSegments () {

			//
		}

		public function underscoredSegments () {

			//
		}

		public function optionalPlaceholder () {

			//
		}
	}
?>
<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Routing\PathPlaceholders;

	class FlowController extends ServiceCoordinator {

		private $pathPlaceholders;

		public function __construct (PathPlaceholders $pathPlaceholders) {

			$this->pathPlaceholders = $pathPlaceholders;
		}

		public function noFlowHandler () {}

		public function getPostDetails () {

			return [];
		}

		public function preloaded () {}

		public function parentFlow () {}

		public function handleChildFlow () {}

		public function handleCombined () {

			return [];
		}

		public function handleSingleNode () {

			return [];
		}

		public function handleFromService () {

			return [];
		}

		public function handlePipeTo () {

			return [];
		}

		public function handleOneOf () {

			return [];
		}

		public function readFlowPayload () {

			return [

				"id" => $this->pathPlaceholders->getSegmentValue("id")
			];
		}
	}
?>
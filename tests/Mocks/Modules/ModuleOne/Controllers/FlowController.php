<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\DummyModels;

	class FlowController extends ServiceCoordinator {

		private $pathPlaceholders, $dummyModels;

		public function __construct (PathPlaceholders $pathPlaceholders, DummyModels $dummyModels) {

			$this->pathPlaceholders = $pathPlaceholders;

			$this->dummyModels = $dummyModels;
		}

		public function noFlowHandler () {

			return [];
		}

		public function getPostDetails () {

			return [];
		}

		public function preloaded () {

			return [];
		}

		public function parentFlow () {

			return [];
		}

		public function handleChildFlow () {

			return [];
		}

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

		public function readFlowPayload ():array {

			return [

				"id" => $this->pathPlaceholders->getSegmentValue("id")
			];
		}

		public function getsTenModels ():array {

			return [

				"anchor" => $this->dummyModels->fetchModels()
			];
		}
	}
?>
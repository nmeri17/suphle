<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\DummyModels, PayloadReaders\ReadsId};

	class FlowController extends ServiceCoordinator {

		private $dummyModels;

		public function __construct (DummyModels $dummyModels) {

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

		public function readFlowPayload (ReadsId $payloadReader):array {

			return [

				"id" => $payloadReader->getDomainObject()
			];
		}

		public function getsTenModels ():array {

			return [

				"anchor" => $this->dummyModels->fetchModels()
			];
		}
	}
?>
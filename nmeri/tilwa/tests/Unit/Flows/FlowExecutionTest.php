<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{MockFacilitator, ProphecyWrapper};

	use Tilwa\Flows\{FlowHydrator, Structures\RouteUserNode, Previous\SingleNode};

	use Tilwa\Response\ResponseManager;

	class FlowExecutionTest extends IsolatedComponentTest {

	 	use ProphecyWrapper, MockFacilitator, FlowData {

			ProphecyWrapper::setup as prophecySetup;
	 	};

	 	private $responseManager = ResponseManager::class;

	 	public function setUp () {

			parent::setUp();

			$this->prophecySetup();

			$this->indexes = $this->getIndexes();
		}
		
		public function test_executeRequest_triggers_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(true);

			$responseManager = $this->prophesize($this->responseManager);

			$responseManager->handleValidRequest()->shouldBeCalled();
 
 			// when
			$hydrator->setDependencies($responseManager, [])

			->executeRequest();
		}

		private function getHydratorForExecuteRequest (bool $canProcessPath):FlowHydrator {

			return $this->negativeStub(FlowHydrator::class, compact("canProcessPath"));
		}
		
		public function test_invalid_request_doesnt_trigger_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(false);

			$responseManager = $this->prophesize($this->responseManager);

			$responseManager->handleValidRequest()->shouldNotBeCalled();
 
 			// when
			$hydrator->setDependencies($responseManager, [])

			->executeRequest();
		}

		public function test_getNodeFromPrevious() {

			$hydrator = $this->container->getClass(FlowHydrator::class);

			$models = $this->indexesToModels();

			$unitNode = new SingleNode($this->payloadKey);

			// given
			$responseManager = $this->negativeStub($this->responseManager);
			
			$hydrator->setDependencies($responseManager, [

				$this->payloadKey => $models
			]);

			$content = $hydrator->getNodeFromPrevious($unitNode); // when

			$this->assertSame($content, $models);
		}
		
		public function test_will_trigger_underlying_format () {

			// call `runNodes` with pre-configured unitNodes. we wanna confirm it'll hit each of the internals with the arguments they are accustomed to
			// alternatively, you might wanna use the immediate lower layer. test that one then calls the final ones (rather than going directly)
			// will need `$sut->previousResponse` set
		}
	}
?>
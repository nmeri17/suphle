<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Flows\FlowHydrator;

	use Tilwa\Flows\Previous\{CollectionNode, SingleNode};

	use Tilwa\Flows\Structures\{RouteUserNode, ServiceContext, GeneratedUrlExecution};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class FlowExecutionTest extends IsolatedComponentTest {

	 	use FlowData, CommonBinds;

	 	private $rendererManager = RoutedRendererManager::class;

	 	public function setUp ():void {

			parent::setUp();

			$this->indexes = $this->getIndexes();
		}
		
		public function test_executeGeneratedUrl_triggers_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(true);

			$sut = $this->positiveDouble($this->rendererManager, [], [

				"handleValidRequest" => [1, []]
			]);
 
 			// when
			$hydrator->setDependencies($sut, [])->executeGeneratedUrl();
		}

		private function getHydratorForExecuteRequest (bool $canProcessPath):FlowHydrator {

			return $this->negativeDouble(FlowHydrator::class, compact("canProcessPath"));
		}
		
		public function test_invalid_request_doesnt_trigger_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(false);

			$rendererManager = $this->positiveDouble($this->rendererManager, [], [

				"handleValidRequest" => [0, []]
			]);
 
 			// when
			$hydrator->setDependencies($rendererManager, [])

			->executeGeneratedUrl();
		}

		public function test_getNodeFromPrevious() {

			$hydrator = $this->container->getClass(FlowHydrator::class);

			$models = $this->indexesToModels();

			$unitNode = new SingleNode($this->payloadKey);

			// given
			$rendererManager = $this->negativeDouble($this->rendererManager);
			
			$hydrator->setDependencies($rendererManager, [

				$this->payloadKey => $models
			]);

			$content = $hydrator->getNodeFromPrevious($unitNode); // when

			$this->assertSame($content, $models);
		}
		
		/**
		 * @param {value} Nullable since not all collection nodes take a value
		 * 
		 * @dataProvider getCollectionNodes
		*/
		public function test_collection_triggers_underlying_format (CollectionNode $unitNode, string $handler, $value = null ) {

			$sut = $this->getHydratorForRunNode($handler, $value);

			$sut->runNodes($unitNode, "*"); // when
		}

		public function getCollectionNodes ():array {

			$serviceContext = new ServiceContext("Foo", "bar");

			return [
				[$this->createCollectionNode()->pipeTo(), "handlePipe"],

				[$this->createCollectionNode()->oneOf(), "handleOneOf", "ids"],

				[$this->createCollectionNode()->oneOf("concat"), "handleOneOf", "concat"],

				[$this->createCollectionNode()->inRange(), "handleRange"], // too lazy to extract the context from getActions

				[$this->createCollectionNode()->dateRange(), "handleDateRange"],

				[$this->createCollectionNode()->setFromService($serviceContext), "handleServiceSource", $serviceContext]
			];
		}

		private function getHydratorForRunNode (string $handler, $value = null):FlowHydrator {

			// given
			$payload = $this->payloadFromPrevious();

			$parameter = !is_null($value) ? $this->equalTo($value): $this->anything();

			return $this->replaceConstructorArguments(FlowHydrator::class, [], [

				"getNodeFromPrevious" => $payload,

				$handler => $this->positiveDouble(GeneratedUrlExecution::class, [

					"getRenderer" => $this->negativeDouble(BaseRenderer::class, [

						"getRawResponse" => ["foo"]
					])
				])
			], [

				$handler => [1, [$this->equalTo($payload), $parameter]]
			]); // then
		}

		public function test_single_triggers_underlying_format () {

			$argument = "next_page_url";

			$unitNode = (new SingleNode($this->payloadKey))

			->altersQuery($argument);

			$sut = $this->getHydratorForRunNode("handleQuerySegmentAlter", $argument);

			$sut->runNodes($unitNode, "*"); // when
		}
	}
?>
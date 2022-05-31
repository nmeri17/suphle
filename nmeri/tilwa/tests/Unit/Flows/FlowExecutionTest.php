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

	 	private $rendererManager = RoutedRendererManager::class,

	 	$sutName = FlowHydrator::class;

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
			$hydrator->setDependencies($sut, [], "")

			->executeGeneratedUrl();
		}

		private function getHydratorForExecuteRequest (bool $canProcessPath):FlowHydrator {

			return $this->replaceConstructorArguments(

				$this->sutName, [], compact("canProcessPath")
			);
		}
		
		public function test_invalid_request_doesnt_trigger_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(false);

			$rendererManager = $this->positiveDouble($this->rendererManager, [], [

				"handleValidRequest" => [0, []]
			]);
 
 			// when
			$hydrator->setDependencies($rendererManager, [], "")

			->executeGeneratedUrl();
		}

		public function test_getNodeFromPrevious() {

			$hydrator = $this->container->getClass($this->sutName);

			$models = $this->indexesToModels();

			$unitNode = new SingleNode($this->payloadKey);

			// given
			$rendererManager = $this->negativeDouble($this->rendererManager);
			
			$hydrator->setDependencies($rendererManager, [

				$this->payloadKey => $models
			], "");

			$content = $hydrator->getNodeFromPrevious($unitNode); // when

			$this->assertSame($content, $models);
		}
		
		public function test_collection_triggers_underlying_handler () {

			$this->dataProvider([

				[$this, "getCollectionNodes"] // given
			],/**
			 * @param {value} Nullable since not all collection nodes take a value
			*/
			 function (CollectionNode $unitNode, string $handler, $value = null ) {

				$this->getHydratorForRunNode($handler, $value ) // then

				->runNodes($unitNode, "*"); // when
			});
		}

		public function getCollectionNodes ():array {

			return [
				[
					$this->createCollectionNode()->pipeTo(), "handlePipe"
				],

				[
					$this->createCollectionNode()->oneOf(), "handleOneOf", "ids"
				],

				[
					$this->createCollectionNode()->oneOf("concat"),

					"handleOneOf", "concat"
				],

				[$this->createCollectionNode()->inRange(), "handleRange"], // too lazy to extract the context from getActions

				[$this->createCollectionNode()->dateRange(), "handleDateRange"]
			];
		}

		private function generatedResponse ():GeneratedUrlExecution {

			return $this->positiveDouble(GeneratedUrlExecution::class, [
				
				"getRenderer" => $this->negativeDouble(BaseRenderer::class, [

					"getRawResponse" => ["foo"]
				])
			]);
		}

		/**
		 * @param {handlerMethod} Asserts that this was called with $calledWith
		 * @param {leadingArgument} Most handlers accept the stripped down ids as first argument. Handlers that behave differently should use this argument
		 * 
		 * @return A double that stubs necessary properties that allow us test access to the underlying flow handling methods eg handlePipe etc
		*/
		public function getHydratorForRunNode (

			string $handlerMethod, $additionalArgument = null,

			$leadingArgument = null
		):FlowHydrator {

			$response = $this->generatedResponse();

			return $this->replaceConstructorArguments($this->sutName, [], [

				$handlerMethod => [$response]
			], [
				"rendererToStorable" => [1, []], // prevent trying to save

				$handlerMethod => [1, [ // then

					$leadingArgument ?? $this->indexes, // note that the main payload is then stripped down to ids nested in each model

					$additionalArgument ?? $this->anything()
				]]
			])->setDependencies(

				$this->positiveDouble($this->rendererManager),

				$this->payloadFromPrevious(), // given

				""
			);
		}

		public function test_collection_triggers_deferred_handler () {

			// given
			$serviceContext = new ServiceContext("Foo", "bar");

			$unitNode = $this->createCollectionNode()->setFromService($serviceContext);

			$this->getHydratorForRunNode(

				"handleServiceSource", $serviceContext,

				$this->equalTo(null)
			) // then
			->runNodes($unitNode, "*"); // when
		}

		public function test_single_triggers_underlying_format () {

			$handlerMethod = "handleQuerySegmentAlter";

			$leafName = "next_page_url";

			$queryPart = "/hello?foo=bar";

			$unitNode = (new SingleNode($leafName))->altersQuery();

			$sut = $this->replaceConstructorArguments($this->sutName, [], [

				$handlerMethod => $this->generatedResponse()
			], [
				"rendererToStorable" => [1, []],

				$handlerMethod => [1, [ $queryPart]]// then
			])->setDependencies(

				$this->positiveDouble($this->rendererManager),

				[$leafName => $queryPart], // given

				""
			)->runNodes($unitNode, "*"); // when
		}
	}
?>
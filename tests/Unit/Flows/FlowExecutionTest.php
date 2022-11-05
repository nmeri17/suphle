<?php
	namespace Suphle\Tests\Unit\Flows;

	use Suphle\Flows\{FlowHydrator, OuterFlowWrapper};

	use Suphle\Flows\Previous\{CollectionNode, SingleNode};

	use Suphle\Flows\Structures\{RouteUserNode, ServiceContext, GeneratedUrlExecution, PendingFlowDetails};

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Services\DecoratorHandlers\VariableDependenciesHandler;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class FlowExecutionTest extends IsolatedComponentTest {

	 	use FlowData, CommonBinds;

	 	private $sutName = FlowHydrator::class,

	 	$flowDetails;

	 	public function setUp ():void {

			parent::setUp();

			$this->indexes = $this->getIndexes();

			$this->flowDetails = $this->replaceConstructorArguments(PendingFlowDetails::class, [], [

				"getStoredUserId" => OuterFlowWrapper::ALL_USERS
			]);
		}
		
		public function test_executeGeneratedUrl_triggers_controller () {

			$this->decorateHydrator($this->getHydratorForExecuteRequest(true), [ // given

 				RoutedRendererManager::class => $this->positiveDouble(RoutedRendererManager::class, [], [

					"handleValidRequest" => [1, []] // then
				])
 			])
			->executeGeneratedUrl(); // when
		}

		protected function decorateHydrator (FlowHydrator $hydrator, array $dependencies = []):FlowHydrator {

			$hydrator->setRequestDetails([], "");

			return $this->container->whenTypeAny()->needsAny(array_merge(

				[
					RoutedRendererManager::class => $this->positiveDouble(RoutedRendererManager::class),

					PathPlaceholders::class => $this->positiveDouble(PathPlaceholders::class)
	 			], $dependencies
			))
			->getClass(VariableDependenciesHandler::class)

			->examineInstance($hydrator, self::class);
		}

		private function getHydratorForExecuteRequest (bool $canProcessPath):FlowHydrator {

			return $this->replaceConstructorArguments(

				$this->sutName, [], compact("canProcessPath")
			);
		}
		
		public function test_invalid_request_doesnt_trigger_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(false);
 
 			// when
			$this->decorateHydrator($hydrator, [

 				RoutedRendererManager::class => $this->positiveDouble(RoutedRendererManager::class, [], [

					"handleValidRequest" => [0, []]
				])
 			])->executeGeneratedUrl();
		}

		public function test_getNodeFromPrevious() {

			$models = $this->indexesToModels();

			$unitNode = new SingleNode($this->payloadKey);

			// given
			$hydrator = $this->decorateHydrator($this->container->getClass($this->sutName), [

 				RoutedRendererManager::class => $this->negativeDouble(RoutedRendererManager::class)
 			]);

 			$hydrator->setRequestDetails([

				$this->payloadKey => $models
			], "");

			$content = $hydrator->getNodeFromPrevious($unitNode); // when

			$this->assertSame($content, $models);
		}
		
		public function test_collection_triggers_underlying_handler () {

			$this->dataProvider([

				$this->getCollectionNodes(...) // given
			],/**
			 * @param {value} Nullable since not all collection nodes take a value
			*/
			 function (CollectionNode $unitNode, string $handler, $value = null ) {

				$this->getHydratorForRunNode($handler, $value ) // then

				->runNodes($unitNode, $this->flowDetails); // when
			});
		}

		public function getCollectionNodes ():array {

			return [
				[
					$this->createCollectionNode()->pipeTo(), "handlePipe"
				],

				[
					$this->createCollectionNode()->asOne(), "handleAsOne", "ids"
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

			$hydrator = $this->replaceConstructorArguments($this->sutName, [], [

				$handlerMethod => [$response],

				"getNodeFromPrevious" => $this->indexesToModels()
			], [
				"rendererToStorable" => [1, []], // prevent trying to save

				$handlerMethod => [1, [ // then

					$leadingArgument ?? $this->indexes, // note that the main payload is then stripped down to ids nested in each model

					$additionalArgument ?? $this->anything()
				]]
			]);

			$hydrator->setRequestDetails( // given

				$this->payloadFromPrevious(), ""
			);

			return $this->decorateHydrator($hydrator);
		}

		public function test_collection_triggers_deferred_handler () {

			// given
			$serviceContext = new ServiceContext("Foo", "bar");

			$unitNode = $this->createCollectionNode()->setFromService($serviceContext);

			$this->getHydratorForRunNode(

				"handleServiceSource", $serviceContext,

				$this->equalTo(null)
			) // then
			->runNodes($unitNode, $this->flowDetails); // when
		}

		public function test_single_triggers_underlying_format () {

			$handlerMethod = "handleQuerySegmentAlter";

			$leafName = "next_page_url";

			$queryPart = "/hello?foo=bar";

			$unitNode = (new SingleNode($leafName))->altersQuery();

			$hydrator = $this->replaceConstructorArguments($this->sutName, [], [

				$handlerMethod => $this->generatedResponse(),

				"getNodeFromPrevious" => $queryPart
			], [
				"rendererToStorable" => [1, []],

				$handlerMethod => [1, [ $queryPart]]// then
			]);

			$hydrator->setRequestDetails([$leafName => $queryPart], ""); // given

			$this->decorateHydrator($hydrator)

			->runNodes($unitNode, $this->flowDetails); // when
		}
	}
?>
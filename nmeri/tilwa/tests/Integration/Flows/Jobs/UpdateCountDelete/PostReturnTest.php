<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\UpdateCountDelete;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Testing\Condiments\{QueueInterceptor, MockFacilitator};

	use Tilwa\Flows\{OuterFlowWrapper, Jobs\UpdateCountDelete};

	use Tilwa\Flows\Structures\{AccessContext, RouteUserNode};

	use DateTime;

	use DateInterval;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, ModuleOneDescriptor, Config\RouterMock};

	class PostReturnTest extends ModuleLevelTest {

		use QueueInterceptor, MockFacilitator;

		private $resourceUrl = "/posts/5",

		$aMinuteBehind;

		public function setUp ():void {

			parent::setUp();

			$this->aMinuteBehind = (new DateTime)->sub(new DateInterval("PT1M"));
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => FlowRoutes::class
					]);
				})
			];
		}
		
		public function test_empties_cache_entry () {

			$this->defaultJobBehavior(); // given

			$this->assertHandledByFlow($this->resourceUrl); // when

			$this->assertNotHandledByFlow($this->resourceUrl); // then
		}
		
		public function test_wont_empty_cache_entry () {

			$this->makeJob($this->makeAccessContext(
				$this->positiveDouble(

					RouteUserNode::class, ["getMaxHits" => 2]
				) // default [getExpiresAt] + this should retain the node
			))->handle(); // given

			$this->assertHandledByFlow($this->resourceUrl); // when

			$this->assertHandledByFlow($this->resourceUrl); // then
		}
		
		/**
		 * @dataProvider expiredContexts
		*/
		public function test_expired_node_wont_be_handled_by_flow (AccessContext $context) {

			$this->makeJob($context)->handle(); // given

			$this->assertNotHandledByFlow($this->resourceUrl); // then
		}
		
		public function test_clears_only_accessed_but_retains_others () {

			$this->defaultJobBehavior(); // given

			$this->assertHandledByFlow($this->resourceUrl); // when

			// then
			$this->assertNotHandledByFlow($this->resourceUrl);

			$this->assertHandledByFlow("/posts/6");
		}

		protected function expiredContexts ():array {

			return [
				[
					$this->positiveDouble(RouteUserNode::class, [

						"getMaxHits" => 200,

						"getExpiresAt" => $this->aMinuteBehind
					])
				],
				[
					$this->positiveDouble(RouteUserNode::class, [

						"getExpiresAt" => $this->aMinuteBehind
					])
				]
			];
		}

		private function makeJob (AccessContext $context):UpdateCountDelete {

			$jobName = UpdateCountDelete::class;

			return $this->container->whenType($jobName)

			->needs([ get_class($context) => $context ])
			
			->getClass($jobName);
		}

		private function makeAccessContext (RouteUserNode $unitPayload):AccessContext {

			return new AccessContext(
				$this->resourceUrl, $unitPayload,

				new RouteUmbrella ($this->resourceUrl), // by running the job, context will be added or deleted into umbrella as appropriate (so, no need to add it here)

				OuterFlowWrapper::ALL_USERS
			); 
		}

		private function defaultJobBehavior ():void {

			$this->makeJob($this->makeAccessContext(
				
				$this->positiveDouble( RouteUserNode::class, [] )
			))->handle();
		}
	}
?>
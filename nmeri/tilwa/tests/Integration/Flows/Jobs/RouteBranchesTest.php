<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Flows\{FlowHydrator, OuterFlowWrapper, Structures\BranchesContext};

	use Tilwa\Contracts\{CacheManager, Auth\User, Config\Router};

	use Tilwa\Response\Format\{Json, AbstractRenderer};

	use Tilwa\Response\ResponseManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, ModuleOneDescriptor, Config\RouterMock};

	class RouteBranchesTest extends BaseJobGenerator {

		private $container;

		public function setUp () {

			$this->container = $this->firstModuleContainer();
		}

		protected function getModules():array {

			$hydrator = FlowHydrator::class;

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => OriginCollection::class
					])
					->replaceWithMock($hydrator, $hydrator, [

						"executeRequest" => $this->flowGeneratedRenderer()
					]);
				})
			];
		}

		/**
		 * @dataProvider contextParameters
		*/
		public function test_stores_correct_data_in_cache (BranchesContext $context) {

			// given => see setup
			$this->makeJob($context)->handle(); // When

			$umbrella = $this->container->getClass(CacheManager::class)

			->get("categories/5");

			$this->assertNotNull($umbrella);

			$this->assertSame( // then
				$umbrella->getUserPayload("*")->getRenderer(),

				$this->flowGeneratedRenderer()
			);
		}

		/**
		 * The test this goes into doesn't do any auth related stuff. It is content with running the flow and expecting to find it in the cache
		 * 
		 * @return [
			 * 	BranchesContext => configured to match what we expect an origin url to populate a task with
			 * url => the flow link expected to enable us access the given task
		 * ]
		*/
		public function contextParameters ():array {

			$responseManager = $this->negativeStub(ResponseManager::class); // stubbing since the information this naturally expects to carry is too contextual to be pulled from just a container

			$user = $this->makeUser(5);

			$renderer = $this->getLoadedRenderer("all_categories", "categories/id");

			return [
				[new BranchesContext($renderer, null, $this->getModules(), null)],

				[new BranchesContext($renderer, $user, null, $responseManager)],

				[new BranchesContext($renderer, $user, $this->getModules(), null)]
			];
		}

		private function flowGeneratedRenderer ():AbstractRenderer {

			return new Json("generatedRenderer");
		}

		/**
		 * @dataProvider contextParameters
		*/
		public function test_will_be_handled_by_flow (BranchesContext $context) {

			// given => see dataProvider
			$this->makeJob($context)->handle(); // When
			
			// then
			$this->assertHandledByFlow("/categories/5"); // Note: we can get away with not even creating an endpoint for this since if the above call behaves correctly, request won't even go there
			
			$wrapper = $this->container->getClass(OuterFlowWrapper::class);

			$this->assertSame($wrapper->handlingRenderer(), $this->flowGeneratedRenderer());
		}

		public function test_no_flow_does_nothing () {

			$this->assertNotPushedToFlow("/no-flow");
		}
	}
?>
<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Flows\{OuterFlowWrapper, Structures\RouteUmbrella};

	use Tilwa\Flows\Structures\BranchesContext;

	use Tilwa\Contracts\{IO\CacheManager, Config\Router, Presentation\BaseRenderer};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class IntraModuleTest extends JobFactory {

		private $user5Url = "/user-content/5";

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => OriginCollection::class
					]);
				})
			];
		}

		public function test_stores_correct_data_in_cache () {

			$this->dataProvider([

				[$this, "contextParameters"]
			], function (BranchesContext $context) {

				// given => see setup
				$this->makeJob($context)->handle(); // When

				$umbrella = $this->container->getClass(CacheManager::class)

				->getItem(OuterFlowWrapper::FLOW_PREFIX . $this->user5Url); // it saves content for all given indexes, not just 5. This means that "/user-content/8" is available and will return 8

				$this->assertNotNull($umbrella);

				$this->assertSame( // then
					$this->expectedResponse(),

					$this->extractResponse(

						$umbrella, $context->getUserId()
					)
				);
			});
		}

		/**
		 * The test this goes into doesn't do any auth related stuff. It is content with running the flow and expecting to find it in the cache
		 * 
		 * @return [
			 * 	BranchesContext => configured to match what we expect an origin url to populate a task with
		 * ]
		*/
		public function contextParameters ():array {

			return [
				[$this->makeBranchesContext()],

				[$this->makeBranchesContext($this->makeUser(5))]
			];
		}

		private function expectedResponse ():array {

			return [

				"id" => 5
			];
		}

		private function extractResponse (RouteUmbrella $routeUmbrella, string $userId):array {

			return $routeUmbrella->getUserPayload($userId)

			->getRenderer()->getRawResponse();
		}

		public function test_will_be_handled_by_flow () {

			$this->dataProvider([

				[$this, "contextParameters"]
			], function (BranchesContext $context) {

				// given => see dataProvider
				$this->makeJob($context)->handle(); // When
				
				// then
				$this->assertHandledByFlow($this->user5Url);
			});
		}

		public function test_no_flow_does_nothing () {

			$this->assertNotPushedToFlow("/no-flow");
		}
	}
?>
<?php
	namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

	use Suphle\Flows\{UmbrellaSaver, Structures\RouteUmbrella};

	use Suphle\Flows\Structures\PendingFlowDetails;

	use Suphle\Contracts\{IO\CacheManager, Config\Router, Presentation\BaseRenderer};

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

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
			], function (PendingFlowDetails $context) {

				// given => see setup
				$this->makeRouteBranches($context)->handle(); // When

				$flowSaver = $this->container->getClass(UmbrellaSaver::class);

				$location = $flowSaver->getPatternLocation($this->user5Url);
				
				$umbrella = $flowSaver->getExistingUmbrella($location); // since it saves content for all given indexes, not just 5. This means that "/user-content/8" is available and will return 8

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
			 * 	PendingFlowDetails => configured to match what we expect an origin url to populate a task with
		 * ]
		*/
		public function contextParameters ():array {

			return [
				[$this->makePendingFlowDetails()],

				[
					$this->makePendingFlowDetails($this->contentOwner),

					$this->contentOwner
				]
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
			], function (PendingFlowDetails $context) {

				// given => see dataProvider
				$this->makeRouteBranches($context)->handle(); // When
				
				// then
				$this->assertHandledByFlow($this->user5Url);
			});
		}

		public function test_no_flow_does_nothing () {

			$this->assertNotPushedToFlow("/no-flow");
		}
	}
?>
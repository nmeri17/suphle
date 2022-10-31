<?php
	namespace Suphle\Tests\Integration\Flows\Jobs\UpdateCountDelete;

	use Suphle\Contracts\{Config\Router, Auth\AuthStorage};

	use Suphle\Flows\{OuterFlowWrapper, Jobs\UpdateCountDelete};

	use Suphle\Flows\Structures\{AccessContext, RouteUserNode, RouteUmbrella};

	use Suphle\Hydration\Structures\ObjectDetails;

	use Suphle\Response\Format\Json;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{ Routes\Flows\FlowRoutes, Meta\ModuleOneDescriptor, Config\RouterMock };

	use DateTime, DateInterval;

	class FlowRoutesUpdateCountTest extends JobFactory {

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

			$this->handleUpdateCountDelete(); // given and when

			$this->assertNotHandledByFlow($this->resourceUrl); // then
		}

		private function handleUpdateCountDelete ():void {

			$this->makeUpdateCountDelete($this->makeAccessContext(
				
				$this->replaceConstructorArguments( RouteUserNode::class , $this->userNodeArguments())
			))->handle(); // will push it into cache storage since hit is still =0

			$this->get($this->resourceUrl); // push delete task to queue

			$this->processQueuedTasks(); // execute delete task
		}

		private function userNodeArguments ():array {

			return [

				"renderer" => $this->replaceConstructorArguments (Json::class, [], [

					"getController" => $this->positiveDouble(ServiceCoordinator::class)
				])
			];
		}

		private function makeAccessContext (RouteUserNode $unitPayload):AccessContext {

			$container = $this->getContainer();

			$objectMeta = $container->getClass(ObjectDetails::class);

			$routeUmbrella = new RouteUmbrella ($this->resourceUrl, $objectMeta);

			$routeUmbrella->setAuthMechanism(get_class(

				$container->getClass(AuthStorage::class)
			));

			return new AccessContext(
				$this->resourceUrl, $unitPayload,

				$routeUmbrella,

				OuterFlowWrapper::ALL_USERS
			); 
		}

		private function makeUpdateCountDelete ($dependency):UpdateCountDelete {

			$jobName = UpdateCountDelete::class;

			return $this->getContainer()->whenType($jobName)

			->needsArguments([ $dependency::class => $dependency ])
			
			->getClass($jobName);
		}
		
		public function test_wont_empty_cache_entry () {

			$this->makeUpdateCountDelete($this->makeAccessContext(
				$this->replaceConstructorArguments(

					RouteUserNode::class, $this->userNodeArguments(),

					["getMaxHits" => 2]
				) // default [getExpiresAt] + this should retain the node
			))->handle(); // given

			$this->assertHandledByFlow($this->resourceUrl); // when

			$this->assertHandledByFlow($this->resourceUrl); // then
		}
		
		public function test_expired_node_wont_be_handled_by_flow () {

			$this->dataProvider([

				$this->expiredContexts(...)
			], function (RouteUserNode $payload) {

				$this->makeUpdateCountDelete( $this->makeAccessContext($payload))->handle(); // given

				$this->assertNotHandledByFlow($this->resourceUrl); // then
			});
		}

		public function expiredContexts ():array {

			return [
				[
					$this->replaceConstructorArguments(RouteUserNode::class, $this->userNodeArguments(),

						[

						"getMaxHits" => 200,

						"getExpiresAt" => $this->aMinuteBehind
					])
				],
				[
					$this->replaceConstructorArguments(RouteUserNode::class, $this->userNodeArguments(), [

						"getExpiresAt" => $this->aMinuteBehind
					])
				]
			];
		}
	}
?>
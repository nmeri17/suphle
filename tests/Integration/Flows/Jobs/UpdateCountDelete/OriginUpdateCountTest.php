<?php
	namespace Suphle\Tests\Integration\Flows\Jobs\UpdateCountDelete;

	use Suphle\Contracts\Config\Router;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class OriginUpdateCountTest extends JobFactory {

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => OriginCollection::class
					]);
				})
			];
		}
		
		public function test_clears_only_accessed_but_retains_others () {

			// given
			$this->handleDefaultPendingFlowDetails(); // pretend to make original request that injects our flow urls

			// when
			$this->get($this->userUrl); // get and remove 5

			$this->processQueuedTasks();

			// then
			$this->assertNotHandledByFlow($this->userUrl);

			$this->assertHandledByFlow("/user-content/6"); // this is still available since flows are stored by url, not by user
		}
	}
?>
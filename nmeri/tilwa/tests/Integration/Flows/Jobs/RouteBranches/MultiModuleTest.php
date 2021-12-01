<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Flows\Structures\BranchesContext;

	use Mockery;

	class MultiModuleTest extends JobFactory {

		protected $originDataName = "post_titles",

		$flowUrl = "posts/id"; // the name used here is determined by the pattern name at the target module

		protected function getModules():array {

			return [

				$this->getModuleOne(), $this->getModuleTwo()
			];
		}

		private function getModuleOne ():ModuleDescriptor {

			return $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

				//
			});
		}

		private function getModuleTwo ():ModuleDescriptor {

			return $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

				$container->replaceWithMock(Router::class, RouterMock::class, [

					"browserEntryRoute" => FlowRoutes::class
				])
				->replaceWithConcrete(FlowHydrator::class, $this->mockHydrator());
			});
		}
		
		public function test_handle_flows_in_other_modules () {

			// given => see module injection

			$this->makeJob(
				new BranchesContext(
					$this->getLoadedRenderer(),

					null, $this->getModules(), null
				)
			)->handle(); // when

			$sut = $this->mockHydrator();

			// then
			$sut->shouldHaveReceived()->executeRequest();

			$sut->shouldHaveReceived()

			->setDependencies(
				$this->getModuleTwo()->getContainer()->getClass(ResponseManager::class)
			);
		}

		private function mockHydrator():FlowHydrator {

			return Mockery::spy(FlowHydrator::class);
		}
	}
?>
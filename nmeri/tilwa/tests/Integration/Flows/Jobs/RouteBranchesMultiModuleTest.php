<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, ModuleOneDescriptor, Config\RouterMock};

	use Mockery;

	class RouteBranchesMultiModuleTest extends BaseJobGenerator {

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
					$this->getLoadedRenderer(

						"post_titles", "posts/id" // the name used here is determined by the pattern name at the target module
					),
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

			Mockery::close();
		}

		private function mockHydrator():FlowHydrator {

			return Mockery::spy(FlowHydrator::class);
		}
	}
?>
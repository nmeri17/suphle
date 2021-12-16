<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Prophecy\Argument\Token\AnyValuesToken;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Flows\Structures\BranchesContext;

	class MultiModuleTest extends JobFactory {

		protected $originDataName = "post_titles",

		$flowUrl = "posts/id"; // the name used here is determined by the pattern name at the target module

		private $mockFlowHydrator;

		protected function setUp () {

			parent::setUp();

			$this->mockFlowHydrator = $this->getProphet()->prophesize(FlowHydrator::class);
		}

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
				->replaceWithConcrete(FlowHydrator::class, $this->mockFlowHydrator->reveal());
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

			$sut = $this->mockFlowHydrator;

			// then
			$sut->executeRequest()->shouldBeCalled();

			$sut->setDependencies(
				$this->getModuleTwo()->getContainer()->getClass(ResponseManager::class),

				new AnyValuesToken
			)
			->shouldBeCalled();
		}
	}
?>
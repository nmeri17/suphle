<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Flows\Structures\BranchesContext;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, Config\RouterMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Meta\ModuleThreeDescriptor;

	class MultiModuleTest extends JobFactory {

		protected $originDataName = "post_titles",

		$flowUrl = "posts/id"; // the name used here is determined by the pattern name at the target module

		private $mockFlowHydrator, $sutName = FlowHydrator::class;

		public function setUp ():void {

			$this->mockFlowHydrator = $this->positiveDouble($this->sutName);

			parent::setUp();
		}

		protected function getModules():array {

			return [

				$this->moduleOne, $this->moduleThree
			];
		}

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicateModule(
				ModuleThreeDescriptor::class,

				function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => FlowRoutes::class
					])
					->replaceWithConcrete($this->sutName, $this->mockFlowHydrator);
				}
			)->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}
		
		public function test_handle_flows_in_other_modules () {

			/*	1) Give FlowRoutes to module 3
				2) getLoadedRenderer stubs a renderer containing one of the routes in FlowRoutes/module 3, meaning it should be handled by RouteBranches (ostensibly, at the end of the request)
			*/

			$this->makeJob(
				new BranchesContext(
					$this->getLoadedRenderer(),

					null, $this->modules, null
				)
			)->handle(); // when

			$this->mockCalls([ // then

				"executeRequest" => [1, []],

				"setDependencies" => [1, [

					$this->moduleThree->getContainer()->getClass(ResponseManager::class),

					$this->anything()
				]]
			], $this->mockFlowHydrator);
		}
	}
?>
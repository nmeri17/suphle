<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Flows\{FlowHydrator, Structures\BranchesContext};

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\{Config\Router, Presentation\BaseRenderer};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, Config\RouterMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Meta\ModuleThreeDescriptor;

	class MultiModuleTest extends JobFactory {

		protected $originDataName = "post_titles",

		$flowUrl = "posts/id"; // the name used here is determined by the pattern name at the target module

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
					]);
				}
			)->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}
		
		// currently fails because jobs are being hydrated from first module. we can update container, but then test won't be useful. refactor to make actual requests
		public function test_handle_flows_in_other_modules () {

			$this->markTestIncomplete();

			/*	1) Give FlowRoutes to module 3
				2) getPrecedingRenderer stubs a renderer containing one of the routes in FlowRoutes/module 3, meaning it should be handled by RouteBranches (ostensibly, at the end of the request)
			*/

			$this->prepareAllModules();

			$container = $this->moduleThree->getContainer();

			$sutName = FlowHydrator::class;

			$container->whenTypeAny()->needsAny([

				$sutName => $this->replaceConstructorArguments($sutName, [], [

					"updatePlaceholders" => $this->returnSelf()
				], [ // then

					"executeGeneratedUrl" => [1, []],

					"setDependencies" => [1, [

						$this->callback(function ($subject) {

							return $subject instanceof RoutedRendererManager;
						}),

						$this->anything()
					]]
				])
			]);

			$this->handleDefaultBranchesContext(); // when
		}
	}
?>
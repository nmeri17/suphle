<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs;

	use Tilwa\Response\Format\{Json, AbstractRenderer};

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Condiments\{QueueInterceptor, MockFacilitator};

	use Illuminate\Support\Collection;

	use Tilwa\Flows\{ ControllerFlows, Jobs\RouteBranches, Structures\BranchesContext};

	class BaseJobGenerator extends ModuleLevelTest {

		use QueueInterceptor, MockFacilitator;

		/**
		 * Stub out the renderer for an imaginary previous request before the flow one we are about to make
		*/
		protected function getLoadedRenderer (string $originDataName, string $flowUrl):AbstractRenderer {

			$renderer = new Json("preloaded");

			$models = [];

			for ($i=0; $i < 10; $i++) $models[] = ["id" => $i]; // the list the flow is gonna iterate over

			$renderer->setRawResponse([

				$originDataName => new Collection($models)
			]);

			$renderer->setFlow($this->constructFlow($originDataName, $flowUrl));

			return $renderer;
		}

		protected function constructFlow (string $originDataName, string $flowUrl):ControllerFlows {

			$flow = new ControllerFlows;

			$flow->linksTo($flowUrl, $flow->previousResponse()
				->collectionNode($originDataName)

				->eachAttribute("id")->pipeTo(),
			);

			return $flow;
		}

		protected function makeJob (BranchesContext $context):RouteBranches {

			$jobName = RouteBranches::class;

			return $this->firstModuleContainer()->whenType($jobName)

			->needs([

				BranchesContext::class => $context
			])
			->getClass($jobName);
		}
	}
?>
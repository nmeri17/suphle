<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Flows\{ ControllerFlows, Jobs\RouteBranches, Structures\BranchesContext};

	use Tilwa\Contracts\{Auth\UserContract, Presentation\BaseRenderer};

	use Tilwa\Response\Format\Json;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Condiments\QueueInterceptor;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\FlowController;

	use Illuminate\Support\Collection;

	abstract class JobFactory extends ModuleLevelTest {

		use QueueInterceptor {

			QueueInterceptor::setUp as queueSetup;
		}

		private $container;

		protected $userUrl = "/user-content/5", // corresponds to the content generated after using [flowUrl] to create a context

		$originDataName = "all_users", $flowUrl = "user-content/id",

		$rendererController = FlowController::class;

		protected function setUp ():void {

			$this->queueSetup();

			$this->container = $this->firstModuleContainer();
		}

		/**
		 * Stub out the renderer for an imaginary previous request before the flow one we are about to make
		*/
		protected function getLoadedRenderer ():BaseRenderer {

			$models = [];

			for ($i=0; $i < 10; $i++) $models[] = ["id" => $i]; // the list the flow is gonna iterate over

			return $this->positiveDouble (Json::class, [

				"getRawResponse" => [

					$this->originDataName => new Collection($models)
				],

				"getFlow" => $this->constructFlow(),

				"getController" => $this->rendererController

			], [], ["preloaded"]);
		}

		protected function constructFlow ():ControllerFlows {

			$flow = new ControllerFlows;

			return $flow->linksTo($this->flowUrl, $flow->previousResponse()
				
				->collectionNode($this->originDataName)->pipeTo(),
			);
		}

		protected function makeJob (BranchesContext $context):RouteBranches {

			$jobName = RouteBranches::class;

			return $this->container->whenType($jobName)

			->needs([ get_class($context) => $context ])

			->getClass($jobName);
		}

		protected function makeUser (int $id):UserContract {

			$entity = $this->container->getClass(UserContract::class);

			$entity->setId($id);

			return $entity;
		}

		protected function makeBranchesContext (?UserContract $user):BranchesContext {

			return new BranchesContext(

				$this->getLoadedRenderer(),

				$user, // creates 10 content models, but assigns the given user as their owner

				$this->getModules(), null
			);
		}
	}
?>
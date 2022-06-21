<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Flows\{ ControllerFlows, Jobs\RouteBranches, Structures\PendingFlowDetails};

	use Tilwa\Contracts\{Auth\UserContract, Presentation\BaseRenderer, Database\OrmDialect};

	use Tilwa\Response\Format\Json;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Testing\Condiments\{QueueInterceptor, BaseDatabasePopulator};

	use Tilwa\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Controllers\FlowController, Concretes\Services\DummyModels};

	abstract class JobFactory extends DescriptorCollection {

		use QueueInterceptor, BaseDatabasePopulator {

			BaseDatabasePopulator::setUp as databaseSetup;
		}

		protected $container,

		$userUrl = "/user-content/5", // corresponds to the content generated after using [flowUrl] to create a context

		$flowUrl = "user-content/id", // this is expected to exist in one of the module entry collections

		$originDataName = "all_users",

		$rendererController = FlowController::class;

		protected function setUp ():void {

			$this->databaseSetup();

			$this->catchQueuedTasks();

			$this->container = $this->firstModuleContainer();
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		protected function getInitialCount ():int {

			return 5;
		}

		/**
		 * Stub out the renderer for an imaginary previous request before the flow one we are about to make
		*/
		protected function getPrecedingRenderer ():BaseRenderer {

			return $this->positiveDouble (Json::class, [

				"getRawResponse" => [

					$this->originDataName => (new DummyModels)->fetchModels() // the list the flow is gonna iterate over
				],

				"getFlow" => $this->constructFlow(),

				"getController" => $this->positiveDouble($this->rendererController)

			], [], ["handler" => "preloaded"]);
		}

		protected function constructFlow ():ControllerFlows {

			$flow = new ControllerFlows;

			return $flow->linksTo($this->flowUrl, $flow->previousResponse()
				
				->collectionNode($this->originDataName)->pipeTo(),
			);
		}

		protected function makeRouteBranches (PendingFlowDetails $context):RouteBranches {

			$jobName = RouteBranches::class;

			$jobInstance = $this->container->whenType($jobName)

			->needsArguments([ PendingFlowDetails::class => $context ])

			->getClass($jobName);

			$this->container->refreshClass($jobName);

			return $jobInstance;
		}

		protected function makeUser (int $id):UserContract {

			return $this->replicator->getExistingEntities(1, compact("id"))[0];
		}

		protected function makePendingFlowDetails (?UserContract $user = null):PendingFlowDetails {

			return new PendingFlowDetails(

				$this->getPrecedingRenderer(),

				$user // creates a collection of 10 models in preceding renderer, then assigns the given user as their owner in the flow we are going to make
			);
		}

		/**
		 * Push in user-content/1-10
		*/
		protected function handleDefaultPendingFlowDetails ():void {

			$this->makeRouteBranches($this->makePendingFlowDetails())->handle();
		}
	}
?>
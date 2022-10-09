<?php
	namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

	use Suphle\Flows\{ ControllerFlows, Jobs\RouteBranches, Structures\PendingFlowDetails};

	use Suphle\Contracts\{Auth\UserContract, Presentation\BaseRenderer, Database\OrmDialect};

	use Suphle\Response\Format\Json;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Testing\Condiments\{QueueInterceptor, BaseDatabasePopulator};

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\FlowController, Concretes\Services\DummyModels};

	abstract class JobFactory extends DescriptorCollection {

		use QueueInterceptor, BaseDatabasePopulator {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		protected $container,

		$userUrl = "/user-content/5", // corresponds to the content generated after using [flowUrl] to create a context

		$flowUrl = "user-content/id", // this is expected to exist in one of the module entry collections

		$originDataName = "all_users",

		$rendererController = FlowController::class;

		protected function setUp ():void {

			$this->databaseAllSetup();

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

		protected function preDatabaseFreeze ():void {

			$this->replicator->modifyInsertion(10); // we'll visit one of them after connection resets
		}

		protected function makeUser ():UserContract {

			return $this->replicator->getRandomEntity();
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
<?php
	namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

	use Suphle\Flows\{ ControllerFlows, Jobs\RouteBranches, Structures\PendingFlowDetails};

	use Suphle\Contracts\{Auth\UserContract, Presentation\BaseRenderer, Database\OrmDialect};

	use Suphle\Response\Format\Json;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Testing\Condiments\{QueueInterceptor, BaseDatabasePopulator};

	use Suphle\Testing\Proxies\SecureUserAssertions;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\FlowCoordinator, Concretes\Services\DummyModels};

	/**
	 * This doesn't send the originating requests. It helps for mocking the task of an originated flow, executing that task, then verifying its behavior under certain conditions
	*/
	abstract class JobFactory extends DescriptorCollection {

		use QueueInterceptor, BaseDatabasePopulator, SecureUserAssertions {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		protected $container;
  protected $contentOwner;
  protected $contentVisitor;
  protected $userUrl = "/user-content/5"; // corresponds to the content generated after using [flowUrl] to create a context
  protected $flowUrl = "user-content/id"; // this is expected to exist in one of the module entry collections
  protected $originDataName = "all_users";
  protected $rendererController = FlowCoordinator::class;

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

				"getCoordinator" => $this->positiveDouble($this->rendererController)

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

			$this->replicator->modifyInsertion(10); // we'll visit as one of them after connection resets

			[$this->contentOwner, $this->contentVisitor] = $this

			->replicator->getRandomEntities(2);
		}

		protected function makePendingFlowDetails (?UserContract $user = null, string $storageName = null):PendingFlowDetails {

			$storage = $this->getAuthStorage($storageName);

			if (!is_null($user)) {

				$storage->startSession($user->getId()); // creates a collection of 10 models in preceding renderer, then assigns the given user as their owner in the flow we are going to make
			
				$storage->setHydrator($this->container->getClass(

					OrmDialect::class
				)->getUserHydrator());
			}
			
			else $storage->logout();

			return new PendingFlowDetails(

				$this->getPrecedingRenderer(),

				$storage
			);
		}

		/**
		 * Push in user-content/1-10, the amount returned from previous payload
		*/
		protected function handleDefaultPendingFlowDetails ():PendingFlowDetails {

			$context = $this->makePendingFlowDetails();

			$this->makeRouteBranches($context)->handle();

			return $context;
		}
	}
?>
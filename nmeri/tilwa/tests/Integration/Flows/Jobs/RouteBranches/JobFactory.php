<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Flows\{ ControllerFlows, Jobs\RouteBranches, Structures\BranchesContext};

	use Tilwa\Contracts\{Auth\UserContract, Presentation\BaseRenderer, Database\OrmDialect};

	use Tilwa\Response\Format\Json;

	use Tilwa\Testing\Condiments\QueueInterceptor;

	use Tilwa\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\FlowController;

	use Illuminate\Support\Collection;

	abstract class JobFactory extends DescriptorCollection {

		use QueueInterceptor {

			QueueInterceptor::setUp as queueSetup;
		}

		protected $container,

		$userUrl = "/user-content/5", // corresponds to the content generated after using [flowUrl] to create a context

		$flowUrl = "user-content/id", // this is expected to exist in one of the module entry collections

		$originDataName = "all_users",

		$rendererController = FlowController::class;

		protected function setUp ():void {

			$this->queueSetup();

			$this->container = $this->firstModuleContainer();
		}

		/**
		 * Stub out the renderer for an imaginary previous request before the flow one we are about to make
		*/
		protected function getPrecedingRenderer ():BaseRenderer {

			$models = [];

			for ($i=1; $i < 11; $i++) $models[] = ["id" => $i]; // the list the flow is gonna iterate over

			return $this->positiveDouble (Json::class, [

				"getRawResponse" => [

					$this->originDataName => new Collection($models)
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

		protected function makeJob (BranchesContext $context):RouteBranches {

			$jobName = RouteBranches::class;

			return $this->container->whenType($jobName)

			->needs([ get_class($context) => $context ])

			->getClass($jobName);
		}

		protected function makeUser (int $id):UserContract {

			$model = $this->container->getClass(OrmDialect::class)->userModel();

			$model->setId($id);

			return $model;
		}

		protected function makeBranchesContext (?UserContract $user):BranchesContext {

			return new BranchesContext(

				$this->getPrecedingRenderer(),

				$user, // creates 10 content models, but assigns the given user as their owner

				$this->modules
			);
		}
	}
?>
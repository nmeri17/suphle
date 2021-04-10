<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\{Authenticator, QueueManager};

	abstract class ModuleAssembly {

		private $container;
		
		abstract public function getModules():array;
		
		public function orchestrate():void {

			$this->bootInterceptor();

			echo $this->beginRequest();
		}
		
		private function bootInterceptor():void {

			new EnvironmentDefaults;

			(new ModuleLevelEvents)->bootReactiveLogger($this->getModules());
		}
		
		private function beginRequest():string {

			$requestPath = $_GET['tilwa_request'];

			$this->setContainer();

			$queueManager = $this->container->getClass(QueueManager::class);

			$flowWrapper = new OuterFlowWrapper($requestPath, $queueManager, $this->getModules());

			if ($flowWrapper->matchesUrl())

				return $this->flowRequestHandler($flowWrapper);

			return (new ModuleToRoute)

			->findContext($this->getModules(), $requestPath)

			->trigger();
		}

		private function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$user = $this->container->getClass(Authenticator::class)->getUser();

			$wrapper->setContext($user);

			$response = $wrapper->getResponse();
				
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		private function setContainer():Container {

			$randomModule = current($this->getModules());

			$this->container = $randomModule->getContainer();
		}
	}
?>
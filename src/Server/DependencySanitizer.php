<?php
	namespace Suphle\Server;

	use Suphle\Server\Structures\DependencyRule;

	use Suphle\Contracts\{Queues\Task, Modules\ControllerModule};

	use Suphle\IO\{Http\BaseHttpRequest, Mailing\MailBuilder};

	use Suphle\Request\PayloadStorage;

	use Suphle\Services\{ServiceCoordinator, UpdatefulService, UpdatelessService, ConditionalFactory};

	use Suphle\Services\Structures\{ModelfulPayload, ModellessPayload};

	use Suphle\Services\DependencyRules\{OnlyLoadedByHandler, ActionDependenciesValidator, ServicePreferenceHandler};

	class DependencySanitizer {

		protected array $rules = [];

		private string $executionPath; // not setting it should throw an error

		public function __construct (

			private readonly FileSystemReader $fileSystemReader,

			private readonly Container $container,

			private readonly ObjectDetails $objectMeta
		) {

			//
		}

		public function setExecutionPath (string $executionPath):void {

			$this->executionPath = $executionPath;
		}

		protected function setDefaultRules ():void {

			$this->coordinatorConstructor();

			$this->coordinatorActionMethods();

			$this->protectUpdateyServices();

			$this->protectMailBuilders();
		}

		/**
		 * @param {rules} DependencyRules[]
		*/
		public function setAllRules (array $rules):void {

			$this->rules = $rules;
		}

		public function cleanseConsumers ():void {

			if (empty($this->rules)) $this->setDefaultRules();

			$hydratedHandlers = array_map(function ($rule) {

				return $rule->extractHandler($this->container);
			}, $this->rules);

			foreach ($hydratedHandlers as $index => $handler)

				$this->iterateExecutionPath($handler, $this->rules[$index]);
		}

		protected function iterateExecutionPath (DependencyFileHandler $handler, DependencyRule $dependencyRule):void {

			$this->fileSystemReader->iterateDirectory(

				$this->executionPath, function ($directoryPath, $directoryName) {

					//
				},

				$this->iteratedFileHandler($handler, $dependencyRule),

				function ($path) {

					//
				}
			);
		}

		protected function iteratedFileHandler (DependencyFileHandler $handler, DependencyRule $dependencyRule):callable {

			return function ($filePath, $fileName) use ($handler, $dependencyRule) {

				$currentClasses = get_declared_classes();

				require_once $filePath;

				$loadedClass = array_diff(get_declared_classes(), $currentClasses);

				$classFullName = current($loadedClass);

				if ($dependencyRule->shouldEvaluateClass($classFullName))

					$handler->evaluateClass($classFullName);
			};
		}

		protected function coordinatorConstructor ():void {

			$this->addRule(
				ServicePreferenceHandler::class,

				$this->coordinatorFilter(...),

				[
					ConditionalFactory::class, // We're treating it as a type of service in itself
					ControllerModule::class, // These are a service already. There's no need accessing them through another local proxy

					PayloadStorage::class, // there may be items we don't want to pass to the builder but to a service?

					BaseHttpRequest::class, UpdatefulService::class,

					UpdatelessService::class
				]
			);
		}

		protected function coordinatorActionMethods ():void {

			$this->addRule(
				ActionDependenciesValidator::class,

				$this->coordinatorFilter(...),

				[

					ModelfulPayload::class, ModellessPayload::class
				]
			);
		}

		protected function coordinatorFilter (string $className):bool {

			return $this->objectMeta->stringInClassTree(

				$className, ServiceCoordinator::class
			);
		}

		public function addRule ():void {

			$this->rules[] = new DependencyRule($ruleHandler, $filter, $argumentList);
		}

		protected function protectUpdateyServices ():void {

			$this->addRule(
				ServicePreferenceHandler::class,

				function ($className):bool {

					return $this->objectMeta->stringInClassTree(

						$className, UpdatefulService::class
					);
				},

				[UpdatelessService::class]
			);

			$this->addRule(
				ServicePreferenceHandler::class,

				function ($className):bool {

					return $this->objectMeta->stringInClassTree(

						$className, UpdatelessService::class
					);
				},

				[UpdatefulService::class]
			);
		}

		protected function protectMailBuilders ():void {

			$this->addRule(
				OnlyLoadedByHandler::class,

				function ($className):bool {return true;},

				[MailBuilder::class, [Task::class]]
			);
		}
	}
?>
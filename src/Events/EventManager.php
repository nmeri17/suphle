<?php
	namespace Suphle\Events;

	use Suphle\Hydration\Structures\ObjectDetails;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Contracts\{Events, Modules\DescriptorInterface, Requests\RequestEventsListener};

	use Suphle\Request\RequestDetails;

	use Suphle\Services\Decorators\{BindsAsSingleton, VariableDependencies};

	use InvalidArgumentException;

	#[BindsAsSingleton(Events::class)]
	#[VariableDependencies(["setModuleDescriptor", "setParentManager"])]
	class EventManager implements Events {

		private const LOCAL_SCOPE = "local",

		EXTERNAL_SCOPE = "external";

		private ModuleLevelEvents $parentManager;

		private DescriptorInterface $moduleDescriptor;

		private array $emitters = [

			self::LOCAL_SCOPE => [], self::EXTERNAL_SCOPE => []
		];

		public function __construct (protected readonly ObjectDetails $objectMeta) {

			//
		}

		/**
		 * @param {module}: Descriptor for the module where this handler will be emitting from. It's also not injected through the constructor since that would prevent its internal test i.e. during module binding stage, obviously, no descriptor is available
		*/
		public function setModuleDescriptor (DescriptorInterface $module):void {

			$this->moduleDescriptor = $module;
		}

		/**
		 * @param {parentManager}: Using a setter instead of a constructor for this to avoid circular dependency during hydration since that parent is the one who eventually hydrates this
		*/
		public function setParentManager ( ModuleLevelEvents $parentManager):void {

			$this->parentManager = $parentManager;
		}

		protected function local (string $emittingEntity, string $handlingClass):EventSubscription {
			
			return $this->initializeHandlingScope(

				self::LOCAL_SCOPE, $emittingEntity, $handlingClass
			);
		}

		protected function external(string $interaction, string $handlingClass):EventSubscription {

			return $this->initializeHandlingScope(

				self::EXTERNAL_SCOPE, $interaction, $handlingClass
			);
		}

		/**
		 * There's a distinction between local and external emitters because we don't wanna assume each client has a hard dependency on that interface. The client shouldn't care beyond the knowledge that such interface may emit such events if it exists
		*/
		private function initializeHandlingScope (

			string $scope, string $emitable, string $handlingClass
		):EventSubscription {

			if ($emitable == $handlingClass)

				throw new InvalidArgumentException("Cannot listen to events emitted by '$emitable' on the same class");

			return $this->emitters[$scope][$emitable] = new EventSubscription(

				$handlingClass, $this->moduleDescriptor->getContainer()
			);
		}

		/**
		 * {@inheritdoc}
		 **/
		public function emit(string $emitter, string $eventName, $payload = null):void {

			$localHandlers = $this->getLocalHandler($emitter);

			$moduleIdentifier = $this->moduleDescriptor->exportsImplements();

			$this->parentManager->triggerHandlers($emitter, $localHandlers, $eventName, $payload)

			->gatherForeignSubscribers($moduleIdentifier) // this means external listeners of this module can comfortably listen to the module exports interface rather than bothering about the specific entity emitting the event
			
			->triggerExternalHandlers($moduleIdentifier, $eventName, $payload);
		}

		/**
		 * For each module, [parentManager] will request handlers matching currently evaluated module from this guy
		 *
		 **/
		public function getExternalHandlers(string $evaluatedModule):?EventSubscription {

			foreach ($this->emitters[self::EXTERNAL_SCOPE] as $emitable => $context)

				if ($emitable == $evaluatedModule)

					return $context;

			return null;
		}

		/**
		 * we want to decouple the emitter from the interface consumers are subscribed to
		 *
		 **/
		private function getLocalHandler (string $emitter):?EventSubscription {
			
			foreach ($this->emitters[self::LOCAL_SCOPE] as $emitable => $details) {

				if ($this->objectMeta->stringInClassTree(

					$emitter, $emitable
				))

					return $details;
			}
			return null;
		}

		public function registerListeners ():void {

			$this->local(RequestDetails::class, RequestEventsListener::class)

			->on(RequestDetails::ON_REFRESH, "handleRefreshEvent");
		}
	}
?>
<?php
	namespace Suphle\Events;

	use Suphle\Hydration\Structures\{ObjectDetails, BaseSingletonBind};

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Events\Structures\HandlerPath;

	use Suphle\Contracts\{Events, Modules\DescriptorInterface, Requests\RequestEventsListener};

	use Suphle\Request\RequestDetails;

	use Suphle\Services\Decorators\BindsAsSingleton;

	use InvalidArgumentException;

	#[BindsAsSingleton]
	class EventManager implements Events {

		protected HandlerPath $activeHandlerPath;

		protected ModuleLevelEvents $parentManager;

		protected array $emitters = ["local" => [], "external" => []];

		/**
		 * @param {module}: Descriptor for the module where this handler will be emitting from
		*/
		public function __construct (

			protected readonly DescriptorInterface $module,

			protected readonly ObjectDetails $objectMeta
		) {

			//
		}

		/**
		 * Using a setter instead of a constructor for this to avoid circular dependency during hydration since that parent equally hydrates this
		*/
		public function setParentManager (ModuleLevelEvents $parentManager):void {

			$this->parentManager = $parentManager;
		}

		public function local (string $emittingEntity, string $handlingClass):self {
			
			$this->initializeHandlingScope("local", $emittingEntity, $handlingClass);

			return $this;
		}

		public function external(string $interaction, string $handlingClass):self {

			$this->initializeHandlingScope("external", $interaction, $handlingClass);

			return $this;
		}

		/**
		 * There's a distinction between local and external emitters because we don't wanna assume each client has a hard dependency on that interface. The client shouldn't care beyond the knowledge that such interface may emit such events if it exists
		*/
		private function initializeHandlingScope(string $scope, string $emitable, string $handlingClass):void {

			if ($emitable == $handlingClass)

				throw new InvalidArgumentException("Cannot listen to events emitted by '$emitable' on the same class");

			$this->emitters[$scope][$emitable] = new EventSubscription($handlingClass, $this->module->getContainer());

			$this->activeHandlerPath = new HandlerPath($emitable, $scope);
		}

		/**
		 * {@inheritdoc}
		 **/
		public function emit(string $emitter, string $eventName, $payload = null):void {

			$localHandlers = $this->getLocalHandler($emitter);

			$moduleIdentifier = $this->module->exportsImplements();

			$this->parentManager->triggerHandlers($emitter, $localHandlers, $eventName, $payload)

			->gatherForeignSubscribers($moduleIdentifier) // this means external listeners of this module can comfortably listen to the module exports interface rather than bothering about the specific entity emitting the event
			
			->triggerExternalHandlers($moduleIdentifier, $eventName, $payload);
		}

		/**
		* @param {eventNames} space separated list of events to be handled by this method
		*/
		public function on(string $eventNames, string $handlingMethod):self {

			$eventList = explode(" ", $eventNames);

			foreach ($eventList as $eventName) {

				$path = $this->activeHandlerPath;
				
				$this->emitters[$path->getScope()]

				[$path->getEmittable()]
				
				->addUnit( trim($eventName), $handlingMethod);
			}

			return $this;
		}

		/**
		 * For each module, [parentManager] will request handlers matching currently evaluated module from this guy
		 *
		 **/
		public function getExternalHandlers(string $evaluatedModule):?EventSubscription {

			foreach ($this->emitters["external"] as $emitable => $context)

				if ($emitable == $evaluatedModule)

					return $context;

			return null;
		}

		/**
		 * we want to decouple the emitter from the interface consumers are subscribed to
		 *
		 **/
		public function getLocalHandler(string $emitter):?EventSubscription {
			
			foreach ($this->emitters["local"] as $emitable => $details) {

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
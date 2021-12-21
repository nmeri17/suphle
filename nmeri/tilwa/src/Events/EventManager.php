<?php
	namespace Tilwa\Events;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Events\Structures\HandlerPath;

	abstract class EventManager {

		private $emitters = ["local" => [], "external" => []];

		private $activeHandlerPath, $module, $parentManager;

		function __construct(ModuleDescriptor $module, ModuleLevelEvents $parentManager) {

			$this->module = $module;

			$this->parentManager = $parentManager;
		}

		public function local(string $emittingEntity, string $handlingClass):self {
			
			$this->initializeHandlingScope("local", $emittingEntity, $handlingClass);

			return $this;
		}

		public function external(string $interaction, string $handlingClass):self {

			$this->initializeHandlingScope("external", $interaction, $handlingClass);

			return $this;
		}

		// there's a distinction between local and external emitters because we don't wanna assume each client has a hard dependency on that interface. The client shouldn't care beyond the knowledge that such interface may emit such events if it exists
		private function initializeHandlingScope(string $scope, string $emitable, string $handlingClass):void {

			$this->emitters[$scope][$emitable] = new EventSubscription($handlingClass, $this->module->getContainer());

			$this->activeHandlerPath = new HandlerPath($emitable, $scope);
		}

		/**
		 * @param {$emitter} inserting this without a proxy means a random class can trigger handlers listening on another event, which is not the best
		 **/
		public function emit(string $emitter, string $eventName, $payload = null) {

			$localHandlers = $this->getLocalHandler($emitter);

			$this->parentManager->triggerHandlers($localHandlers, $eventName, $payload)

			->gatherForeignSubscribers($this->module->exportsImplements()) // this means external listeners of this module can comfortably listen to the module exports interface rather than bothering about the specific entity emitting the event
			
			->triggerExternalHandlers($eventName, $payload);
		}

		/**
		* @param {eventNames} space separated list of events to be handled by this method
		*/
		public function on(string $eventNames, string $handlingMethod):self {

			foreach (explode(" ", $eventNames) as $eventName) {

				$path = $this->activeHandlerPath;
				
				$this->emitters[$path->getScope()]

				[$path->getEmittable()]
				
				->addUnit( trim($eventName), $handlingMethod);
			}
		}

		/**
		 * For each module, [parentManager] will request handlers matching currently evaluated module from this guy
		 *
		 **/
		public function getExternalHandlers(string $evaluatedModule):EventSubscription {

			foreach ($this->emitters["external"] as $emitable => $context)

				if ($emitable == $evaluatedModule)

					return $context;
		}

		/**
		 * we want to decouple the emitter from the interface consumers are subscribed to
		 *
		 **/
		public function getLocalHandler(string $emitter):EventSubscription {
			
			foreach ($this->emitters["local"] as $emitable => $details) {

				$parents = class_implements($emitter);

				if (array_key_exists($emitable, $parents) || $emitable == $emitter)

					return $details;
			}
		}

		abstract public function registerListeners():void;
	}
?>
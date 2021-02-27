<?php
	namespace Tilwa\Events;

	use Tilwa\App\ParentModule;

	abstract class EventManager {

		private $emitters;

		private $activeHandlerPath;

		private $module;

		private $externalHandlers;

		function __construct(ParentModule $module, array $externalHandlers) {

			$this->module = $module;

			$this->externalHandlers = $externalHandlers;
			
			$this->emitters = ["local" => [], "external" => []];
		}

		public function local(string $emittingInterface, string $handlingClass):self {
			
			$this->initializeHandlingScope("local", $emittingInterface, $handlingClass);
		}

		private function initializeHandlingScope(string $scope, string $emittingInterface, string $handlingClass):void {

			$handlingUnits = [];

			$this->emitters[$scope][$emittingInterface] = compact("handlingClass", "handlingUnits"); // all events from this interface should be handled by only one class

			$this->activeHandlerPath = compact("scope", "emittingInterface");
		}

		// [REVIEW] this means the emitter must equally trigger using its foreign name so external listeners can react
		public function external(string $interaction, string $handlingClass):self {

			$this->initializeHandlingScope("external", $interaction, $handlingClass);
		}

		/**
		 * @param {$emitter} inserting this without a proxy means a random class can trigger handlers listening on another class, which is not the best
		 **/
		public function emit(string $emitter, string $eventName, $payload) {

			foreach ([
				$this->hydrateLocalScope($emitter),

				$this->externalHandlers // assumes the handling class has already been hydrated via `getExternalHandlers`
			] as $scope)

				$this->triggerHandlers($scope, $eventName, $payload);
		}

		public function triggerHandlers(array $scope, string $eventName, $payload) {
			
			$hydratedHandler = $scope["handlingClass"];

			foreach ($scope["handlingUnits"] as $executionUnit) {

				$method = $executionUnit["handlingMethod"];
				
				if ($eventName == $executionUnit["eventName"])

					$hydratedHandler->$method($payload);
			}
		}

		private function hydrateLocalScope(string $emitter):array {

			$scope = $this->getLocalHandler($emitter);

			$scope["handlingClass"] = $this->module->container->getClass($scope["handlingClass"]);
			
			return $scope;
		}

		public function on(string $eventName, string $handlingMethod):self {

			$activeScope = $this->activeHandlerPath["scope"];

			$emitter = $this->activeHandlerPath["emittingInterface"];
			
			array_push(
				$this->emitters[$activeScope][$emitter]["handlingUnits"], compact("eventName", "handlingMethod")
			);
		}

		/**
		 * For each module, ModuleToRoute will request handlers matching currently evaluated module from this guy
		 *
		 * @return hydrates the class listening on the currently evaluated module and returns the scope
		 **/
		public function getExternalHandlers(string $evaluatedModule):array {

			foreach ($this->emitters["external"] as $emitable => $context) {

				if ($emitable == $evaluatedModule) {

					$context["handlingClass"] = $this->module->container->getClass($context["handlingClass"]);

					return $context;
				}
			}
		}

		/**
		 * we want to decouple the emitter from the interface consumers are subscribed to
		 *
		 * @return Array of the listening context and its listeners
		 **/
		public function getLocalHandler(string $emitter):array {
			
			foreach ($this->emitters["local"] as $emitable => $details) {

				$parents = class_implements($emitter);

				if (array_key_exists($emitable, $parents) || $emitable == $emitter)

					return $details;
			}
		}

		abstract public function registerListeners():void;
	}
?>
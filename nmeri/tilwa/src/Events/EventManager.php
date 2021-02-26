<?php
	namespace Tilwa\Events;

	use Tilwa\App\ParentModule;

	class EventManager {

		private $emitters;

		private $activeHandlerPath;

		private $module;

		function __construct(ParentModule $module) {

			$this->module = $module;
			
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

		public function external(string $interaction, string $handlingClass):self {

			if (array_key_exists($interaction, $this->module->getDependsOn()))
			
				$this->initializeHandlingScope("external", $interaction, $handlingClass);
		}

		public function emit( string $eventName, $payload) {
			# code...
		}

		public function on(string $eventName, string $handlingMethod):self {

			$activeScope = $this->activeHandlerPath["scope"];

			$emitter = $this->activeHandlerPath["emittingInterface"];
			
			array_push(
				$this->emitters[$activeScope][$emitter]["handlingUnits"], compact("eventName", "handlingMethod")
			);
		}

		abstract public function registerListeners():void;
	}
?>
<?php
	namespace Suphle\Hydration\Structures;

	class NamespaceUnit {

		/**
		*	@param {nameResolver} Function(string $requestedInterface):string
		*	[requestedInterface] has the namespace trimmed away. It's the entity requested at runtime
		*/
		private $fromNamespace, $newLocation, $nameResolver;

		function __construct(string $fromNamespace, string $newLocation, callable $nameResolver) {

			$this->fromNamespace = $fromNamespace;

			$this->nameResolver = $nameResolver;

			$this->newLocation = $newLocation;
		}

		public function getSource():string {
			
			return $this->fromNamespace;
		}

		public function getNewName(string $incomingEntity):string {
			
			return call_user_func($this->nameResolver, $incomingEntity);
		}

		public function getLocation():string {
			
			return $this->newLocation;
		}
	}
?>
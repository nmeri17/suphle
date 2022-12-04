<?php
	namespace Suphle\Hydration\Structures;

	class NamespaceUnit {

		public function __construct(

			private string $fromNamespace,

			private string $newLocation,

			/**
	   		* @param {nameResolver} Function(string $requestedInterface):string
			*	[requestedInterface] has the namespace trimmed away. It's the entity requested at runtime
			*/
			private callable $nameResolver
		) {

			//
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
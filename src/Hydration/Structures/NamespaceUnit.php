<?php
	namespace Suphle\Hydration\Structures;

	class NamespaceUnit {

		/**
   *	@param {nameResolver} Function(string $requestedInterface):string
   *	[requestedInterface] has the namespace trimmed away. It's the entity requested at runtime
   */
  private $nameResolver;

		function __construct(/**
   *	@param {nameResolver} Function(string $requestedInterface):string
   *	[requestedInterface] has the namespace trimmed away. It's the entity requested at runtime
   */
  private readonly string $fromNamespace, /**
   *	@param {nameResolver} Function(string $requestedInterface):string
   *	[requestedInterface] has the namespace trimmed away. It's the entity requested at runtime
   */
  private readonly string $newLocation, callable $nameResolver) {

			$this->nameResolver = $nameResolver;
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
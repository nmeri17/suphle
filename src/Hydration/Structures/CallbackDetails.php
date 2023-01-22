<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Hydration\Container;

	use Opis\Closure\{SerializableClosure, serialize, unserialize};

	class CallbackDetails {

		public function __construct (

			protected readonly Container $container
		) {

			//
		}

		/**
		 * Autowires each callback level
		 * 
		 * @param {toBind} When present, callback will access its protected properties
		 * 
		 * @return mixed. Any value returned that's not a callback
		*/
		public function recursiveValueDerivation (callable $outerFunction, object $toBind = null) {

			$parameters = $this->container->getMethodParameters($outerFunction);

			if (!is_null($toBind))

				$outerFunction = $outerFunction->bindTo($toBind, $toBind /*access protected properties*/);

			$result = call_user_func_array($outerFunction, $parameters);

			while (is_callable($result))

				$result = $this->recursiveValueDerivation($result);

			return $result;
		}

		public function freezeFunction (?callable $callback):?string {

			if (is_null($callback)) return null;

			return serialize(new SerializableClosure($callback));
		}

		public function hydrateSerialized (?string $serialized):?callable {

			if (is_null($serialized)) return null;

			return unserialize($serialized)->getClosure();
		}
	}
?>
<?php
	namespace Suphle\Response\Format;

	use Suphle\Hydration\Structures\CallbackDetails;

	use Suphle\Services\Decorators\VariableDependencies;

	use Opis\Closure\{SerializableClosure, serialize, unserialize};

	#[VariableDependencies([ "setCallbackDetails" ])]
	class Redirect extends GenericRenderer {

		protected $destination;

		protected CallbackDetails $callbackDetails;

		/**
		 * @param {destination} Since PDO instances can't be serialized, when using this renderer with PDO in scope, wrap this parameter in a curried/doubly wrapped function
		 
		 Arguments for the eventual function are autowired and the return value is used as new request location

		 Function is bound to this object instance
		*/
		public function __construct(protected string $handler, callable $destination) {

			$this->destination = serialize(new SerializableClosure($destination)); // liquefy it so it can be cached later under previous requests

			$this->statusCode = 302;
		}

		public function setCallbackDetails (CallbackDetails $callbackDetails):void {

			$this->callbackDetails = $callbackDetails;
		}

		public function render ():string {
			
			$deserialized = unserialize($this->destination)->getClosure();

			return $this->headers["Location"] = $this->callbackDetails

			->recursiveValueDerivation($deserialized);
		}
	}
?>
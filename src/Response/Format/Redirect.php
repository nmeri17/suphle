<?php
	namespace Suphle\Response\Format;

	use Suphle\Hydration\Structures\CallbackDetails;

	use Suphle\Services\Decorators\VariableDependencies;

	use Suphle\Request\PayloadStorage;

	use Closure;

	#[VariableDependencies([ "setCallbackDetails" ])]
	class Redirect extends GenericRenderer {

		public const STATUS_CODE = 302;

		protected CallbackDetails $callbackDetails;

		protected int $statusCode = self::STATUS_CODE;

		/**
		 * @param {destination} Since PDO instances can't be serialized, when using this renderer with PDO in scope, wrap this parameter in a curried/doubly wrapped function
		 
		 Arguments for the eventual function are autowired and the return value is used as new request location

		 Function is bound to this object instance
		*/
		public function __construct (

			protected string $handler, protected ?Closure $destination
		) {

			//
		}

		public function setCallbackDetails (CallbackDetails $callbackDetails):void {

			$this->callbackDetails = $callbackDetails;
		}

		protected function renderRedirect (callable $callback):string {

			return $this->headers[PayloadStorage::LOCATION_KEY] = $this->callbackDetails->recursiveValueDerivation($callback);
		}

		public function render ():string {
			
			return $this->renderRedirect($this->destination);
		}

		public function isSerializable ():bool {

			return false;
		}
	}
?>
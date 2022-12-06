<?php
	namespace Suphle\Contracts\Services\CallInterceptors;

	interface ServiceErrorCatcher {

		/**
		 * Translate the error resulting from a deliberate action such as [findOrFail], to one of the exceptions handled in the exception config
		 * 
		 * Rethrowing Exceptions prevent them from bubbling down to [failureState]
		 * 
		 * @return [CustomError::class => NotFoundException::class]
		*/
		public function rethrowAs ():array;

		/**
		 * Indicate to callers that operation failed
		 * 
		 * @return Value to give the caller in cases were execution shouldn't terminate; meaning it must correspond to method's return value
		*/
		public function failureState (string $method);

		public function lastErrorMethod ():?string;

		public function matchesErrorMethod (string $method):bool;

		public function didHaveErrors (string $method):void;

		public function getDebugDetails ();
	}
?>
<?php
	namespace Tilwa\Contracts\Services\Decorators;

	use Tilwa\Services\Structures\OptionalDTO;

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
		 * @return Value to give the caller in cases were execution shouldn't terminate
		*/
		public function failureState (string $method):OptionalDTO;
	}
?>
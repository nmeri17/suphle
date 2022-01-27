<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface ServiceErrorCatcher {

		/**
		 * Translate the error resulting from a deliberate action such as [findOrFail], to one of the exceptions handled in the exception config
		 * 
		 * @return [CustomError::class => NotFoundException::class]
		*/
		public function rethrowAs ():array;
	}
?>
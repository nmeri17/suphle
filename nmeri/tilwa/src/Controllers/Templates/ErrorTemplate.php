<?php
	namespace Tilwa\Controllers\Templates;

	use Tilwa\Controllers\Decorators\ServiceErrorCatcher;

	class <NewName> extends <OldName> {

		private $errorCatcher, $originalTarget;

		public function __construct ( <CatcherType> $errorCatcher, ServiceErrorCatcher $originalTarget) {

			$this->errorCatcher = $errorCatcher;

			$this->originalTarget = $originalTarget;
		}

		<Methods>
	}
?>
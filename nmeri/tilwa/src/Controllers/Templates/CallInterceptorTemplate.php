<?php
	namespace Tilwa\Controllers\Templates;

	class <NewName> extends <OldName> {

		private $catcherType, $originalTarget;

		public function __construct ( <CatcherType> $catcherType, <OldName> $originalTarget) {

			$this->catcherType = $catcherType;

			$this->originalTarget = $originalTarget;
		}

		<Methods>
	}
?>
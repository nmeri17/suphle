<?php
	namespace Suphle\Modules\Structures;

	/**
	 * Using this to avoid pulling ModuleHandlerIdentifier into lower level classes just to access app modules
	*/
	class ActiveDescriptors {

		private $descriptors;

		public function __construct (array $descriptors) {

			$this->descriptors = $descriptors;
		}

		public function getDescriptors ():array {

			return $this->descriptors;
		}
	}
?>
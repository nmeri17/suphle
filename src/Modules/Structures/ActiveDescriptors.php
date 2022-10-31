<?php
	namespace Suphle\Modules\Structures;

	/**
	 * Using this to avoid pulling ModuleHandlerIdentifier into lower level classes just to access app modules
	*/
	class ActiveDescriptors {

		public function __construct(private readonly array $descriptors)
  {
  }

		public function getDescriptors ():array {

			return $this->descriptors;
		}
	}
?>
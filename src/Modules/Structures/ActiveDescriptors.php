<?php
	namespace Suphle\Modules\Structures;

	use Suphle\Hydration\Container;

	/**
	 * Using this to avoid pulling ModuleHandlerIdentifier into lower level classes just to access app modules
	*/
	class ActiveDescriptors {

		public function __construct(protected readonly array $originalDescriptors) {

			//
		}

		public function firstOriginalContainer ():Container {

			return current($this->originalDescriptors)->getContainer();
		}

		public function getOriginalDescriptors ():array {

			return $this->originalDescriptors;
		}
	}
?>
<?php
	namespace Tilwa\Exception\Explosives;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Exception;

	class Unauthenticated extends Exception {

		private $storage;

		public function __construct (AuthStorage $storage) {

			$this->storage = $storage;
		}

		public function storageMechanism ():AuthStorage {

			return $this->storage;
		}
	}
?>
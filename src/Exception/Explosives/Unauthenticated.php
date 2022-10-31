<?php
	namespace Suphle\Exception\Explosives;

	use Suphle\Contracts\Auth\AuthStorage;

	use Exception;

	class Unauthenticated extends Exception {

		public function __construct(private readonly AuthStorage $storage)
  {
  }

		public function storageMechanism ():AuthStorage {

			return $this->storage;
		}
	}
?>
<?php
	namespace Tilwa\Tests\Mocks\Auth;

	use Tilwa\Contracts\Auth\UserHydrator;

	use stdClass;

	class ArrayUserHydratorMock implements UserHydrator {

		private $users;

		public function __construct () {

			$this->users = [new stdClass, new stdClass];

			$this->setIds();
		}

		private function setIds():void {

			foreach ($this->users as $index => $user)

				$user->id = $index;
		}

		public function findById(string $id) {

			return $this->users[$id];
		}

		public function findAtLogin() {

			return $this->users[1];
		}
	}
?>
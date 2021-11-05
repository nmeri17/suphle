<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Auth\{User, AuthStorage};

	use Tilwa\App\Container;

	trait SecureUserAssertions {

		abstract protected function getContainer ():Container;

		private function getStorage (string $storageName):AuthStorage {

			return $this->getContainer()->getClass($authStorage);
		}

		protected function actingAs(User $user, string $storageName = AuthStorage::class):self {

			$this->getStorage($storageName)->impersonate($user->getId());

			return $this;
		}

		protected function assertAuthenticatedAs(User $user, string $storageName = AuthStorage::class):self {

			$this->assertSame($user, $this->getStorage($storageName)->getUser());

			return $this;
		}

		protected function assertGuest (string $storageName = AuthStorage::class):self {

			$this->assertNull( $this->getStorage($storageName)->getUser());

			return $this;
		}
	}
?>
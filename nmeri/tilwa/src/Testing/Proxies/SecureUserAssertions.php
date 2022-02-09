<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Auth\{User, AuthStorage};

	use Tilwa\Hydration\Container;

	trait SecureUserAssertions {

		abstract protected function getContainer ():Container;

		protected function getAuthStorage (string $storageName = AuthStorage::class):AuthStorage {

			return $this->getContainer()->getClass($authStorage);
		}

		protected function actingAs(User $user, ?string $storageName):self {

			$this->getAuthStorage($storageName)->impersonate($user->getId());

			return $this;
		}

		protected function assertAuthenticatedAs(User $user, string $storageName):self {

			$this->assertSame($user, $this->getAuthStorage($storageName)->getUser());

			return $this;
		}

		protected function assertGuest (string $storageName):self {

			$this->assertNull( $this->getAuthStorage($storageName)->getUser());

			return $this;
		}
	}
?>
<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Auth\{User, AuthStorage};

	use Tilwa\Hydration\Container;

	trait SecureUserAssertions {

		abstract protected function getContainer ():Container;

		protected function getAuthStorage (string $storageName = AuthStorage::class):AuthStorage {

			$container = $this->getContainer();

			$storage = $container->getClass($authStorage);

			$container->whenTypeAny()->needsAny([ // assumes we're overwriting the bound concrete

				AuthStorage::class => $storage
			]);

			return $storage;
		}

		protected function actingAs(User $user, ?string $storageName):self {

			$this->getAuthStorage($storageName)->imitate($user->getId());

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
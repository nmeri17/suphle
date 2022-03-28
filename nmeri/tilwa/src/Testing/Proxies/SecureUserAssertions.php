<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Auth\{UserContract, AuthStorage};

	use Tilwa\Hydration\Container;

	trait SecureUserAssertions {

		abstract protected function getContainer ():Container;

		protected function getAuthStorage (string $storageName):AuthStorage {

			$container = $this->getContainer();

			$container->whenTypeAny()->needsAny([ // assumes we're overwriting the bound concrete

				AuthStorage::class => $storage = $container->getClass($storageName)
			]);

			return $storage;
		}

		protected function actingAs(UserContract $user, string $storageName = AuthStorage::class):self {

			$this->getAuthStorage($storageName)->imitate($user->getId());

			return $this;
		}

		protected function assertAuthenticatedAs(UserContract $user, string $storageName):self {

			$this->assertSame($user, $this->getAuthStorage($storageName)->getUser());

			return $this;
		}

		protected function assertGuest (string $storageName):self {

			$this->assertNull( $this->getAuthStorage($storageName)->getUser());

			return $this;
		}
	}
?>
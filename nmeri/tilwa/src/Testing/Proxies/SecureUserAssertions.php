<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Auth\{UserContract, AuthStorage};

	use Tilwa\Contracts\Database\OrmDialect;

	use ReflectionClass, Exception;

	trait SecureUserAssertions {

		private $genericStorage = AuthStorage::class;

		protected $hasHydratedOrmDialect = false;

		abstract protected function getContainer ():Container;

		/**
		 * @param {storageName} When none is specified, we just want to retrive default authStorage mechanism wired in; otherwise we prefer a more precise assertion
		*/
		protected function getAuthStorage (?string $storageName):AuthStorage {

			if (is_null($storageName))

				$storageName = $this->genericStorage; // work with whichever one was originally bound in container

			elseif ((new ReflectionClass($storageName))->isInterface())

				throw new Exception ("storageName must be a concrete class");

			return $this->ensureHasHydrator($storageName);
		}

		private function ensureHasHydrator (string $storageName):AuthStorage {
			
			$container = $this->getContainer();

			if ($this->hasHydratedOrmDialect)

				return $container->getClass($storageName);

			$ormDialect = $container->getClass(OrmDialect::class);

			$authStorage = $container->getClass($storageName);

			if ($storageName != $this->genericStorage && get_class($authStorage) != $storageName)

				$authStorage->setHydrator($ormDialect->getUserHydrator());

			$this->hasHydratedOrmDialect = true;

			return $authStorage;
		}

		protected function actingAs (UserContract $user, string $storageName = null):self {

			$storage = $this->getAuthStorage($storageName);

			$storage->startSession($user->getId());

			$storage->resumeSession();

			$this->getContainer()->whenTypeAny()->needsAny([

				$this->genericStorage => $storage
			]); // since it doesn't make sense for developer to authenticate to one mechanism while app is running on another

			return $this;
		}

		protected function assertAuthenticatedAs (UserContract $expectedUser, string $storageName = null):self {

			$foundUser = $this->getAuthStorage($storageName)->getUser();

			$this->assertNotNull($foundUser, "No user authenticated");

			$this->assertEquals(

				$expectedUser, $foundUser,

				"Failed asserting authenticated user matches $expectedUser"
			);

			return $this;
		}

		protected function assertGuest (string $storageName = null):self {

			$foundUser = $this->getAuthStorage($storageName)->getUser();

			$this->assertNull(
				$foundUser,

				"Unexprected user $foundUser found");

			return $this;
		}
	}
?>
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
		 * @param {storageName} When none is specified, we just want to retrieve bound authStorage mechanism
		*/
		protected function getAuthStorage (?string $storageName):AuthStorage {

			if (is_null($storageName))

				$storageName = $this->genericStorage; // work with whichever one was originally bound in container

			return $this->ensureHasHydrator($storageName);
		}

		private function ensureHasHydrator (string $storageName):AuthStorage {
			
			$container = $this->getContainer();

			if ($this->hasHydratedOrmDialect)

				return $container->getClass($storageName);

			$ormDialect = $container->getClass(OrmDialect::class);

			$authStorage = $container->getClass($storageName);

			$isNotDefault = $storageName != $this->genericStorage &&

			get_class($authStorage) != $storageName;

			if ($isNotDefault) // the default has hydrator set. If dev wants to test another storage mechanism, we'll do that explicitly

				$authStorage->setHydrator($ormDialect->getUserHydrator());

			$this->hasHydratedOrmDialect = true;

			return $authStorage;
		}

		/**
		 * Will update bound instance of authStorage since it doesn't make sense for developer to authenticate to one mechanism while app is running on another
		*/
		protected function actingAs (UserContract $user, string $storageName = null):string {

			$storage = $this->getAuthStorage($storageName);

			$identifier = $storage->startSession($user->getId());

			$this->getContainer()->whenTypeAny()->needsAny([

				$this->genericStorage => $storage
			]);

			return $identifier;
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

				"Unexpected user $foundUser found");

			return $this;
		}
	}
?>
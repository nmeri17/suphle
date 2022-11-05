<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Auth\{UserContract, AuthStorage};

	use Suphle\Contracts\Database\OrmDialect;

	trait SecureUserAssertions {

		private $GENERIC_STORAGE = AuthStorage::class; // since traits can't have constants

		abstract protected function getContainer ():Container;

		/**
		 * @param {storageName} When none is specified, we just want to retrieve bound authStorage mechanism
		*/
		protected function getAuthStorage (?string $storageName = null):AuthStorage { // can be called with null (courtesy of member methods receiving null defaults), and no argument

			if (is_null($storageName))

				$storageName = $this->GENERIC_STORAGE;

			$container = $this->getContainer();

			$authStorage = $container->getClass($storageName);

			$authStorage->setHydrator( // in a real app, it's expected that the titular module hydrates OrmDialect, thus triggering this same call under the hood

				$container->getClass(OrmDialect::class)->getUserHydrator()
			);

			return $authStorage;
		}

		/**
		 * Will update bound instance of authStorage since it doesn't make sense for developer to authenticate to one mechanism while app is running on another
		*/
		protected function actingAs (UserContract $user, string $storageName = null):string {

			$storage = $this->getAuthStorage($storageName);

			$identifier = $storage->startSession($user->getId());

			$this->getContainer()->whenTypeAny()->needsAny([ // using this instead of massProvide so it doesn't wipe out any dependencies within a test

				$this->GENERIC_STORAGE => $storage
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
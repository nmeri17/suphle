<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Auth\{UserContract, AuthStorage};

	use ReflectionClass, Exception;

	trait SecureUserAssertions {

		abstract protected function getContainer ():Container;

		/**
		 * @param {storageName} When none is specified, we just want to retrive default authStorage mechanism wired in; otherwise we prefer a more precise assertion
		*/
		protected function getAuthStorage (?string $storageName):AuthStorage {

			if (is_null($storageName))

				$storageName = AuthStorage::class; // work with whichever one was originally bound in container

			elseif ((new ReflectionClass($storageName))->isInterface())

				throw new Exception ("storageName must be a concrete class");

			return $this->getContainer()->getClass($storageName);
		}

		protected function actingAs (UserContract $user, string $storageName = null):self {

			$storage = $this->getAuthStorage($storageName);

			$storage->startSession($user->getId());

			$storage->resumeSession();

			$this->getContainer()->whenTypeAny()->needsAny([

				AuthStorage::class => $storage
			]); // since it doesn't make sense for developer to authenticate to one mechanism while app is running on another

			return $this;
		}

		protected function assertAuthenticatedAs (UserContract $user, string $storageName = null):self {

			$this->assertEquals($user, $this->getAuthStorage($storageName)->getUser());

			return $this;
		}

		protected function assertGuest (string $storageName = null):self {

			$this->assertNull( $this->getAuthStorage($storageName)->getUser());

			return $this;
		}
	}
?>
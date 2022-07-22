<?php
	namespace Suphle\Auth\Storage;

	use Suphle\Contracts\Auth\{AuthStorage, UserContract, UserHydrator};

	/**
	 * Assumes ormDialect has already booted. They should probably be coupled together, but AuthStorage is crucial enough to exist independently (type-hinting, for one)
	*/
	abstract class BaseAuthStorage implements AuthStorage {

		protected $userHydrator, $user, $identifier;

		public function setHydrator (UserHydrator $userHydrator):void {

			$this->userHydrator = $userHydrator;
		}

		public function getUser ():?UserContract {

			$userId = $this->getId();

			if (is_null($userId)

				// || is_null($this->userHydrator) // fail loudly in the absence of a hydrator?
			)

				return null;

			if ( is_null($this->user)) // when accessed for the first time

				$this->user = $this->userHydrator->findById( $userId );

			return $this->user;
		}
		
		public function getId ():?string {

			$this->resumeSession(); // this is supposed to be wrapped in a nonce preventing it from being called each time we try to read id, but that'll mean we can't switch between multiple users within tests e.g those using [dataProvider], where PHPUnit doesn't reset objects

			return $this->identifier;
		}

		/**
		 * @param {value}: target user identifier
		 * @return newly minted token for that id or simply returns same value for session-based mechanism
		*/
		public function imitate (string $value):string {

			$this->identifier = $value;

			$this->discardUser();

			return $this->startSession($value);
		}

		public function logout ():void {

			$this->identifier = null;

			$this->discardUser();
		}

		private function discardUser ():void {

			$this->user = null;
		}
	}
?>
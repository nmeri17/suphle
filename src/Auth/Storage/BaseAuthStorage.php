<?php
	namespace Suphle\Auth\Storage;

	use Suphle\Contracts\Auth\{AuthStorage, UserContract, UserHydrator};

	/**
	 * Assumes ormDialect has already booted. They should probably be coupled together, but AuthStorage is crucial enough to exist independently (type-hinting, for one)
	*/
	abstract class BaseAuthStorage implements AuthStorage {

		protected UserHydrator $userHydrator;
		
		protected ?UserContract $user = null;
		
		protected ?string $identifier = null;

		public function setHydrator (UserHydrator $userHydrator):void {

			$this->userHydrator = $userHydrator;
		}

		/**
		 * {@inheritdoc}
		*/
		public function getUser ():?UserContract {

			$userId = $this->getId();

			if (is_null($userId)) return null;

			if ( is_null($this->user))

				$this->user = $this->userHydrator->getUserById( $userId );

			return $this->user;
		}
		
		public function getId ():?string {

			$this->resumeSession(); // this is supposed to be wrapped in a nonce preventing it from being called each time we try to read id, but that'll mean we can't switch between multiple users within tests e.g those using [dataProvider], where PHPUnit doesn't reset objects

			return $this->identifier;
		}

		/**
		 * {@inheritdoc}
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
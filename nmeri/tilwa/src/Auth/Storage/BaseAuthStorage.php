<?php
	namespace Tilwa\Auth\Storage;

	use Tilwa\Contracts\Auth\{AuthStorage, UserContract, UserHydrator};

	/**
	 * Assumes ormDialect has already booted. They should probably be coupled together, but AuthStorage is crucis enough to exist independently (type-hinting, for one)
	*/
	abstract class BaseAuthStorage implements AuthStorage {

		protected $userHydrator, $user, $identifier;

		public function setHydrator (UserHydrator $userHydrator):void {

			$this->userHydrator = $userHydrator;
		}

		public function getUser ():?UserContract {

			if (is_null($this->identifier) || is_null($this->userHydrator)) return null;

			if ( is_null($this->user)) // when accessed for the first time

				$this->user = $this->userHydrator->findById( $this->identifier );

			return $this->user;
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
		
		public function getId ():string {

			return $this->identifier;
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
<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\Auth\AuthStorage;

	abstract class BaseAuthStorage implements AuthStorage {

		protected $userHydrator, $authConfig, $user, $identifier;

		public function getUser () {

			if (is_null($this->identifier)) return null;

			if ( is_null($this->user)) // when accessed for the first time

				$this->user = $this->userHydrator->findById( $this->identifier );

			return $this->user;
		}

		/**
		 * @param {value}: target user identifier
		 * @return newly minted token for that id or simply returns same value for session-based mechanism
		*/
		public function loginAs (string $value):string {

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
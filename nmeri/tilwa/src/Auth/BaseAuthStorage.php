<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\Auth\AuthStorage;

	abstract class BaseAuthStorage implements AuthStorage {

		protected $userHydrator, $authConfig, $user, $identifier;

		public function getUser () {

			if (is_null($this->identifier))

				return null;

			if ( is_null($this->user)) // when accessed for the first time

				$this->user = $this->userHydrator->findById( $this->identifier );

			return $this->user;
		}

		public function loginAs (string $value) {

			$this->identifier = $value;
		}
		
		public function getId ():string {

			return $this->identifier;
		}

		public function logout ():void {

			$this->identifier = $this->user = null;
		}
	}
?>
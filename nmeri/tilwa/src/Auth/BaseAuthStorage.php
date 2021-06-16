<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\AuthStorage;

	abstract class BaseAuthStorage implements AuthStorage {

		private $claimedPatterns = [];

		protected $userHydrator, $authConfig, $user, $identifier;

		public function claimPatterns (array $paths):void {

			$this->claimedPatterns = array_unique($this->claimedPatterns + $paths);
		}

		public function isClaimedPattern (string $pattern):bool {

			return in_array($pattern, $this->claimedPatterns);
		}

		public function getUser () {

			if (is_null($this->identifier))

				return null;

			if ( is_null($this->user)) // when accessed for the first time

				$this->user = $this->userHydrator->findById( $this->identifier );

			return $this->user;
		}

		public function loginAs (string $value) {

			if ($this->authConfig->isAdmin($this->user))

				$this->identifier = $value;
		}
		
		public function getIdentifier ():string {

			return $this->identifier;
		}

		public function logout ():void {

			$this->identifier = $this->user = null;
		}
	}
?>
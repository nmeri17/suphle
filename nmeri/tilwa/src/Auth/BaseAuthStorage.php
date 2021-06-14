<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\AuthStorage;

	abstract class BaseAuthStorage implements AuthStorage {

		private $claimedPatterns = [];

		private $databaseAdapter, $authConfig;

		protected $user, $identifier;

		public function claimPatterns (array $paths):self {

			$this->claimedPatterns = array_unique($this->claimedPatterns + $paths);
		}

		public function isClaimedPattern (string $pattern):bool {

			return in_array($pattern, $this->claimedPatterns);
		}

		public function getUser () {

			if (is_null($this->identifier))

				return null;

			if ( is_null($this->user))

				$this->user = $this->hydrateUser();

			return $this->user;
		}

		private function hydrateUser () {

			return $this->databaseAdapter->findOne(
				$this->authConfig->getUserModel(),

				$this->identifier
			);
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
<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\LoginActions;

	class ApiAuthRepo implements LoginActions {

		private $comparer, $authStorage;

		public function __construct (EmailPasswordComparer $comparer, TokenStorage $authStorage) { 
			
			$this->comparer = $comparer;

			$this->authStorage = $authStorage;
		}

		public function compareCredentials ():bool {

			$this->comparer->setAuthMechanism($this->authStorage);

			return $this->comparer->compare();
		}

		// session/jwt values are set, depending on auth guard
		public function successLogin () {}

		public function failedLogin () {}
	}
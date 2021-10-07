<?php
	namespace Tilwa\Auth;

	class ApiAuthRepo extends BaseAuthRepo {

		public function __construct (EmailPasswordComparer $comparer, TokenStorage $authStorage) { 
			
			$this->comparer = $comparer;

			$this->authStorage = $authStorage;
		}

		public function successLogin () {

			return [

				"token" => $this->authStorage->startSession($this->comparer->getUser()->getId())
			];
		}
	}
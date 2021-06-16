<?php

	namespace Tilwa\Auth;

	class BrowserAuthRepo extends BaseAuthRepo {

		private $comparer, $authStorage;

		public function __construct (EmailPasswordComparer $comparer, SessionStorage $authStorage) { 
			
			$this->comparer = $comparer;

			$this->authStorage = $authStorage;
		}

		public function successLogin () {

			$this->authStorage->startSession($this->comparer->getUser()->id);
		}
	}
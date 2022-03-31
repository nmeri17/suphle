<?php
	namespace Tilwa\Auth\Repositories;

	use Tilwa\Auth\EmailPasswordComparer;

	use Tilwa\Auth\Storage\SessionStorage;

	class BrowserAuthRepo extends BaseAuthRepo {

		private $authStorage;

		public function __construct (EmailPasswordComparer $comparer, SessionStorage $authStorage) { 
			
			$this->comparer = $comparer;

			$this->authStorage = $authStorage;
		}

		public function successLogin () {

			$this->authStorage->startSession($this->comparer->getUser()->getId());
		}
	}
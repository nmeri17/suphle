<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	use Suphle\Auth\Storage\SessionStorage;

	class BrowserAuthRepo extends BaseAuthRepo {

		private $authStorage;

		public function __construct (ColumnPayloadComparer $comparer, SessionStorage $authStorage) { 
			
			$this->comparer = $comparer;

			$this->authStorage = $authStorage;
		}

		public function successLogin () {

			$this->authStorage->startSession($this->comparer->getUser()->getId());
		}
	}
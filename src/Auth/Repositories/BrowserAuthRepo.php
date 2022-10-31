<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	use Suphle\Auth\Storage\SessionStorage;

	class BrowserAuthRepo extends BaseAuthRepo {

		public function __construct (ColumnPayloadComparer $comparer, private readonly SessionStorage $authStorage) { 
			
			$this->comparer = $comparer;
		}

		public function successLogin () {

			$this->authStorage->startSession($this->comparer->getUser()->getId());
		}
	}
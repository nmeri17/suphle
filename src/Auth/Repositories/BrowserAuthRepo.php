<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	use Suphle\Auth\Storage\SessionStorage;

	class BrowserAuthRepo extends BaseAuthRepo {

		public function __construct (
			protected readonly ColumnPayloadComparer $comparer,

			protected readonly SessionStorage $authStorage
		) { 
			
			//
		}

		public function successLogin () {

			$this->authStorage->startSession($this->comparer->getUser()->getId());
		}
	}
?>
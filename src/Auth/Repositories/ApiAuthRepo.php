<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	class ApiAuthRepo extends BaseAuthRepo {

		private $authStorage;

		public function __construct (ColumnPayloadComparer $comparer, TokenStorage $authStorage) { 
			
			$this->comparer = $comparer;

			$this->authStorage = $authStorage;
		}

		public function successLogin () {

			return [

				"token" => $this->authStorage->startSession($this->comparer->getUser()->getId())
			];
		}
	}
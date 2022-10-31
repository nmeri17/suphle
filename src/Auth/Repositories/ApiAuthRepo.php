<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	class ApiAuthRepo extends BaseAuthRepo {

		public function __construct (ColumnPayloadComparer $comparer, private readonly TokenStorage $authStorage) { 
			
			$this->comparer = $comparer;
		}

		public function successLogin () {

			return [

				"token" => $this->authStorage->startSession($this->comparer->getUser()->getId())
			];
		}
	}
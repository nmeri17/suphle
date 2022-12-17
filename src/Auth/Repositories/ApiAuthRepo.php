<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	class ApiAuthRepo extends BaseAuthRepo {

		public function __construct (

			protected readonly ColumnPayloadComparer $comparer,

			protected readonly TokenStorage $authStorage
		) { 
			
			//
		}

		public function successLogin () {

			return [

				"token" => $this->authStorage->startSession($this->comparer->getUser()->getId())
			];
		}
	}
?>
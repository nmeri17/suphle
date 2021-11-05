<?php

	namespace Tilwa\Contracts\Auth;

	interface AuthStorage {

		public function logout ():void;

		public function impersonate (string $value):string;

		public function getId ():string;

		public function startSession (string $userId):string;

		public function resumeSession ():void;

		public function getUser ():User;
	}
?>
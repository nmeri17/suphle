<?php
	namespace Tilwa\Contracts\Auth;

	interface AuthStorage {

		public function logout ():void;

		public function imitate (string $value):string;

		public function getId ():string;

		public function startSession (string $userId):string;

		public function resumeSession ():void;

		/**
		 * @return null when there's no authenticated user
		*/
		public function getUser ():?UserContract;

		public function setHydrator (UserHydrator $userHydrator):void;
	}
?>
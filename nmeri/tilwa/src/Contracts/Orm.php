<?php

	interface Orm {

		public $userIdentifier;

		protected $connection;

		public function getUser();

		public function findOne();

		public setUserIdentifier ();

		public setConnection();
	}
?>
<?php
	namespace Tilwa\Contracts\Auth;

	interface UserContract {

		public function getId ();

		public function setId ($id):void;

		public function getPassword ();

		/**
		 * Find a model by its primary key.
		 * 
		 * @return Preferably, self
		*/
		public function find($id, $columns = ['*']);
	}
?>
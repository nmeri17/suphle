<?php
	namespace Tilwa\Contracts\Services\Models;

	/**
	 * Migration should create field, like "edit_lock" for these fields to read from
	*/
	interface IntegrityModel {

		public function getEditIntegrity ():int;

		/**
		 * @param {integrity} Will be null on the return leg, after successfully editing resource and unsetting it for other users
		*/
		public function setEditIntegrity (?int $integrity):void;
	}
?>
<?php
	namespace Tilwa\Contracts\Services\Models;

	use DateTime;

	interface IntegrityModel {

		const COLUMN_NAME = "edit_lock"; // Migration should create this column for methods to read from

		/**
		 * If [COLUMN_NAME] is null for this model, user is looking at a stale version
		*/
		public function includesEditIntegrity ():bool;

		/**
		 * Unset all integrities for this model
		*/
		public function nullifyEditIntegrity (DateTime $integrity):void;

		protected function enableAudit ():bool;

		protected function makeHistory ():void;
	}
?>
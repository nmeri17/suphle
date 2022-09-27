<?php
	namespace Suphle\Adapters\Orms\Eloquent\Condiments;

	use Suphle\Adapters\Orms\Eloquent\ComponentEntry;

	trait MigrationLocation {

		protected static $componentEntry;

		public function dependencyMethods ():array {

			return ["setComponentEntry"];
		}

		public function setComponentEntry (ComponentEntry $entry):void {

			self::$componentEntry = $entry;
		}

		public static function migrationFolders ():array {

			return [

				self::$componentEntry->userLandMirror() . "Migrations"
			];
		}
	}
?>
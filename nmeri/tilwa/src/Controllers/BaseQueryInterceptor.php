<?php

	namespace Tilwa\Controllers;

	abstract class BaseQueryInterceptor {

		protected $permissions;

		public function setPermissions(object $modulePermissions):void {
			
			$this->permissions = $modulePermissions;
		}
	}
?>
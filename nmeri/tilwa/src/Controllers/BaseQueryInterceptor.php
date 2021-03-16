<?php

	namespace Tilwa\Controllers;

	abstract class BaseQueryInterceptor {

		protected $permissions;

		abstract public function activeModel():void;

		public function setPermissions(object $modulePermissions):void {
			
			$this->permissions = $modulePermissions;
		}
	}
?>
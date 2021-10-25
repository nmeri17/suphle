<?php
	namespace Tilwa\App;

	abstract class BaseInterfaceLoader {

		/**
		 *  Use for computed arguments that can't simply come from known types
		*/
		public function bindArguments():array {

			return [];
		}

		/**
		 *  boot your object, maybe to configure/prepare it for use
		*/
		public function afterBind($initialized):void {}

		abstract public function concrete():string;
	}
?>
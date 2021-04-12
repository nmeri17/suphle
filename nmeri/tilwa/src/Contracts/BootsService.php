<?php

	namespace Tilwa\Contracts;

	use Tilwa\App\Container;

	interface BootsService {

		/**
		* @description: For services requiring some boot operations before they can be consumed
		*/
		public function setup(Container $container):void;
	}
?>
<?php
	namespace Suphle\Modules;

	use Suphle\Contracts\ConsoleClient;

	class ProjectInitializer {

		public function __construct (

			private readonly ConsoleClient $consoleRunner
		) {

			//
		}

		public function allInitOperations (string $moduleName):void { // create module, templates:install (requires internet for laravel), optional user migrations, rr get-binary, start server (hello world path and test)

			//
		}

		protected function createModule ():void {}
	}
?>
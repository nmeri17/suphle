<?php
	namespace Tilwa\Contracts;

	use Tilwa\Console\BaseCliCommand;

	interface ConsoleClient {

		/**
		 * @return void
		*/
		public function addCommand (BaseCliCommand $command);

		/**
		 * @return void
		*/
		public function run ();

		/**
		 * @return BaseCliCommand
		*/
		public function find (string $name);
	}
?>
<?php
	namespace Suphle\Contracts;

	use Suphle\Console\BaseCliCommand;

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
		public function findCommand (string $name);
	}
?>
<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\Console as ConsoleContract;

	use Suphle\Bridge\Laravel\Cli\ArtisanCli;

	class Console implements ConsoleContract {

		public function commandsList ():array {

			return [
				ArtisanCli::class
			];
		}
	}
?>
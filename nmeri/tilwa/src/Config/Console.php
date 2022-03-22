<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Console as ConsoleContract;

	use Tilwa\Bridge\Laravel\Cli\ArtisanCli;

	class Console implements ConsoleContract {

		public function commandsList ():array {

			return [
				ArtisanCli::class
			];
		}
	}
?>
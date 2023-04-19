<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\Console as ConsoleContract;

	use Suphle\Bridge\Laravel\Cli\ArtisanCli;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Suphle\Server\Commands\HttpServerCommand;

	use Suphle\Routing\Commands\CrudCommand;

	class Console implements ConsoleContract {

		public function commandsList ():array {

			return [
				ArtisanCli::class, CloneModuleCommand::class,

				CrudCommand::class, HttpServerCommand::class,

				InstallComponentCommand::class
			];
		}
	}
?>
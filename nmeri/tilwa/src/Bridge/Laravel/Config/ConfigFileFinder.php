<?php
	namespace Tilwa\Bridge\Laravel\Config;

	use Illuminate\Foundation\Bootstrap\LoadConfiguration;

	use Illuminate\Contracts\{Config\Repository as RepositoryContract, Foundation\Application};

	class ConfigFileFinder extends LoadConfiguration {

		public function loadConfigurationFiles(Application $app, RepositoryContract $repository) {

			parent::loadConfigurationFiles();
		}
	}
?>
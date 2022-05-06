<?php
	namespace Tilwa\Bridge\Laravel\Config;

	use Illuminate\Foundation\Bootstrap\LoadConfiguration;

	use Illuminate\Contracts\{Config\Repository as ConfigRepository, Foundation\Application};

	/**
	 * We methods from the parent to be made public
	*/		
	class ConfigFileFinder extends LoadConfiguration {

		public function getConfigNames (Application $app) {

			return array_map(function ($fullPath) {

				preg_match("/([\w]+)\.\w+$/", $fullPath, $matches);

				return $matches[1];
			
			}, $this->getConfigurationFiles($app));
		}

		public function __call (string $methodName, array $arguments) {

			return call_user_func_array([$this, $methodName], $arguments);
		}
	}
?>
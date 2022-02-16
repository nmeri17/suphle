<?php
	namespace Tilwa\Bridge\Laravel\InterfaceLoaders;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\Config\{ModuleFiles, Laravel as LaravelConfig};

	use Tilwa\Contracts\Bridge\LaravelContainer;

	class LaravelAppLoader extends BaseInterfaceLoader {

		private $fileConfig, $laravelConfig, $configLoader;

		public function __construct (ModuleFiles $fileConfig, LaravelConfig $laravelConfig, ConfigLoader $configLoader) {

			$this->fileConfig = $fileConfig;

			$this->laravelConfig = $laravelConfig;

			$this->configLoader = $configLoader;
		}

		public function bindArguments():array {

			return [

				"basePath" => $this->fileConfig->activeModulePath() . DIRECTORY_SEPARATOR . $this->laravelConfig->frameworkDirectory();
			];
		}

		public function afterBind(LaravelContainer $initialized):void {

			foreach ($this->laravelConfig->interfaceConcretes() as $alias => $concrete)

				$initialized->instance($alias, $concrete);

			(new ConfigFileFinder)

			->loadConfigurationFiles($initialized, $this->configLoader);
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}
	}
?>
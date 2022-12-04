<?php
	namespace Suphle\Bridge\Laravel\InterfaceLoaders;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Bridge\Laravel\{LaravelAppConcrete, ComponentEntry};

	use Suphle\Bridge\Laravel\Config\{ConfigLoader, ConfigFileFinder};

	use Suphle\Contracts\{Config\Laravel as LaravelConfig, Bridge\LaravelContainer};

	class LaravelAppLoader extends BaseInterfaceLoader {

		public function __construct(private readonly ConfigLoader $configLoader, private readonly ComponentEntry $componentEntry)
  {
  }

		public function bindArguments():array {

			return [

				"basePath" => $this->getBasePath()
			];
		}

		public function concreteName ():string {

			return LaravelAppConcrete::class;
		}

		public function afterBind ($initialized):void {

			$this->injectBindings($initialized); // required for below call

			$initialized->ensureHasLoadedHelpers();

			$this->attendToConfig($initialized);

			$initialized->runContainerBootstrappers();
		}

		private function injectBindings (LaravelContainer $laravelContainer):void {

			$laravelContainer->registerConcreteBindings($laravelContainer->concreteBinds());

			$laravelContainer->registerSimpleBindings($laravelContainer->simpleBinds());
		}

		/**
		  * Leaving this here instead of in app bootstrappers so:
			* 1) we can inject custom loader
			* 2) we deliberately want to avoid calling [bootstrap]
		*/
		protected function attendToConfig (LaravelContainer $laravelContainer):void {

			$finder = new ConfigFileFinder;

			$finder->loadConfigurationFiles($laravelContainer, $this->configLoader);

			foreach ($finder->getConfigNames($laravelContainer) as $fileName)

				$this->configLoader->get($fileName); // using this to trigger whatever class overrides are available
		}

		protected function getBasePath ():string {

			return $this->componentEntry->userLandMirror();
		}
	}
?>
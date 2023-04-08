<?php
	namespace Suphle\Bridge\Laravel\InterfaceLoaders;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Bridge\Laravel\{LaravelAppConcrete, ComponentEntry};

	use Suphle\Bridge\Laravel\Config\{ConfigLoader, ConfigFileFinder};

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Illuminate\Support\Facades\Facade;

	class LaravelAppLoader extends BaseInterfaceLoader {

		public function __construct (
			protected readonly ConfigLoader $configLoader,

			protected readonly ComponentEntry $componentEntry
		) {

			//
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

			$initialized->overrideAppHelper();

			$this->attendToConfig($initialized);

			$initialized->runContainerBootstrappers();
		}

		protected function injectBindings (LaravelContainer $laravelContainer):void {
			
			Facade::setFacadeApplication($laravelContainer);

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
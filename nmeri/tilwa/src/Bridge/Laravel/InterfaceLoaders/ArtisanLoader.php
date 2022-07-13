<?php
	namespace Tilwa\Bridge\Laravel\InterfaceLoaders;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Bridge\Laravel\ArtisanConcrete;

	use Illuminate\{Console\Application, Events\Dispatcher};

	class ArtisanLoader extends BaseInterfaceLoader {

		private $laravelContainer;

		public function __construct (LaravelContainer $laravelContainer) {

			$this->laravelContainer = $laravelContainer;
		}

		public function bindArguments ():array {

			$this->laravelContainer->loadDeferredProviders(); // it's important that providers are booted before our concrete is being instantiated, since concrete will expect commands to have already been injected into console, which only happens during booting

			return [
				"laravel" => $this->laravelContainer,

				"events" => $this->laravelContainer->make(Dispatcher::class)
			];
		}

		public function concreteName ():string {

			return ArtisanConcrete::class;
		}
	}
?>
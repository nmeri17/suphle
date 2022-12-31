<?php
	namespace Suphle\Bridge\Laravel\InterfaceLoaders;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Suphle\Bridge\Laravel\ArtisanConcrete;

	use Illuminate\{Console\Application, Events\Dispatcher};

	class ArtisanLoader extends BaseInterfaceLoader {

		public function __construct(protected readonly LaravelContainer $laravelContainer) {

			//
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
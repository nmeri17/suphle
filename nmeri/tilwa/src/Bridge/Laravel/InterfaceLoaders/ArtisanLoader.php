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

			return [
				"laravel" => $this->laravelContainer,

				"events" => $this->laravelContainer->make(Dispatcher::class)
			];
		}

		public function concrete ():string {

			return ArtisanConcrete::class;
		}
	}
?>
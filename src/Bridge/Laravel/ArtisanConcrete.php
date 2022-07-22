<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\Bridge\{LaravelArtisan, LaravelContainer};

	use Illuminate\{Console\Application, Events\Dispatcher};

	class ArtisanConcrete extends Application implements LaravelArtisan {

		public function __construct (LaravelContainer $laravelContainer, Dispatcher $events, string $version) {

			parent::__construct($laravelContainer, $events, $version);
		}

		public function invokeCommand ($command, array $parameters = []):int {

			return $this->call($command, $parameters);
		}
	}
?>
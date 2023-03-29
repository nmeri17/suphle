<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\Bridge\{LaravelArtisan, LaravelContainer};

	use Illuminate\{Console\Application, Events\Dispatcher};

	use Symfony\Component\Console\Output\OutputInterface;

	class ArtisanConcrete extends Application implements LaravelArtisan {

		public function __construct (LaravelContainer $laravelContainer, Dispatcher $events, string $version) {

			parent::__construct($laravelContainer, $events, $version);
		}

		public function invokeCommand ($command, OutputInterface $writeTo, array $parameters = []):int {

			return $this->call($command, $parameters, $writeTo);
		}
	}
?>
<?php
	namespace Suphle\Contracts\Bridge;

	use Symfony\Component\Console\Output\OutputInterface;

	interface LaravelArtisan {

		public function invokeCommand ($command, array $parameters = [], OutputInterface $writeTo):int;
	}
?>
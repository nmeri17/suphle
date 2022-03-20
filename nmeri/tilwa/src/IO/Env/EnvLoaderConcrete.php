<?php
	namespace Tilwa\IO\Env;

	use Tilwa\Contracts\IO\EnvAccessor;

	class EnvLoaderConcrete implements EnvAccessor {

		public function getField (string $name) {

			return getenv($name);
		}
	}
?>
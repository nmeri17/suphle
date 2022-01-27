<?php
	namespace Tilwa\Controllers\Structures;

	use Tilwa\Contracts\Services\Decorators\SelectiveDependencies;

	class UpdatefulService implements SelectiveDependencies {

		public function getPermitted ():array {

			return [];
		}

		public function getRejected ():array {

			return [UpdatelessService::class];
		}
	}
?>
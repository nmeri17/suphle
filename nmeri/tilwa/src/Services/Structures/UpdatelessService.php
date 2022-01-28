<?php
	namespace Tilwa\Services\Structures;

	use Tilwa\Contracts\Services\Decorators\SelectiveDependencies;

	class UpdatelessService implements SelectiveDependencies {

		public function getPermitted ():array {

			return [];
		}

		public function getRejected ():array {

			return [UpdatefulService::class];
		}
	}
?>
<?php
	namespace Suphle\Services;

	use Suphle\Contracts\Services\Decorators\SelectiveDependencies;

	class UpdatefulService implements SelectiveDependencies {

		final public function getPermitted ():array {

			return [];
		}

		final public function getRejected ():array {

			return [UpdatelessService::class];
		}
	}
?>
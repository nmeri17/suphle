<?php
	namespace Suphle\Services;

	use Suphle\Contracts\Services\Decorators\SelectiveDependencies;

	class UpdatelessService implements SelectiveDependencies {

		final public function getPermitted ():array {

			return [];
		}

		final public function getRejected ():array {

			return [UpdatefulService::class];
		}
	}
?>
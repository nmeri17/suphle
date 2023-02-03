<?php
	namespace Suphle\Services\Decorators;

	use Attribute;

	#[Attribute(Attribute::TARGET_CLASS)]
	class VariableDependencies {

		public function __construct (public readonly array $dependencyMethods) {

			//
		}
	}
?>
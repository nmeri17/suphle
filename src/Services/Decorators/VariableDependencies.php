<?php
	namespace Suphle\Services\Decorators;

	use Attribute;

	#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
	class VariableDependencies {

		public function __construct (public readonly array $dependencyMethods) {

			//
		}
	}
?>
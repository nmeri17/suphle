<?php
	namespace Suphle\Services\Decorators;

	use Suphle\Contracts\Services\CallInterceptors\ServiceErrorCatcher;

	use Attribute;

	/**
	 * @throws Exception if target doesn't implement {interceptType}
	*/
	#[Attribute(Attribute::TARGET_CLASS)]
	class InterceptsCalls {

		public function __construct (

			public readonly string $interceptType = ServiceErrorCatcher::class
		) {

			//
		}
	}
?>
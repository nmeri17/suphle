<?php
	namespace Suphle\Services\Decorators;

	use Suphle\Contracts\Response\RendererManager;

	use Attribute;

	/**
	 * The handler for this resides in RendererManager::acquireValidatorStatus. By not using a standalone decorator handler (which would result in it being evaluated whenever action parameters are fetched), {bypassOrganicProcedures} can get away with being called, without triggering inappropriate validation checks
	*/
	#[Attribute(Attribute::TARGET_METHOD)]
	class ValidationRules {

		public function __construct (public readonly array $rules) {

			//
		}
	}
?>
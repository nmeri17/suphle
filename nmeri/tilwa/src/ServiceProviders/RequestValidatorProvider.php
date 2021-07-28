<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\Request\Validators\RakitValidator;

	class RequestValidatorProvider extends ServiceProvider {

		public function concrete():string { // replace this full blown provider with a simple bind

			return RakitValidator::class;
		}
	}
?>
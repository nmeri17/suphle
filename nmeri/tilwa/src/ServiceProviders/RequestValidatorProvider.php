<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\Request\Validators\RakitValidator;

	class RequestValidatorProvider extends ServiceProvider {

		public function concrete():string {

			return RakitValidator::class;
		}
	}
?>
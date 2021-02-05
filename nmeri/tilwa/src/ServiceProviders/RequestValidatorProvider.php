<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\Http\Request\Validators\RakitValidator;

	class RequestValidatorProvider extends ServiceProvider {

		public function concrete():string {

			return RakitValidator::class;
		}
	}
?>
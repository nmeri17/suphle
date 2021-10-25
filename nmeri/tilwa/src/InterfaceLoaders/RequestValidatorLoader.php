<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\Request\Validators\RakitValidator;

	class RequestValidatorLoader extends BaseInterfaceLoader {

		public function concrete():string { // replace this full blown provider with a simple bind

			return RakitValidator::class;
		}
	}
?>
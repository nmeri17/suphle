<?php
	namespace Suphle\Tests\Integration\Modules;

	use Suphle\Modules\ModuleHandlerIdentifier;

	use Suphle\Contracts\Presentation\BaseRenderer;

	trait DoublesHandlerIdentifier {

		protected $dummyRenderer;

		protected function setDummyRenderer ():void {

			$this->dummyRenderer = $this->positiveDouble(BaseRenderer::class, [

				"getRawResponse" => [],

				"getStatusCode" => 200
			]);
		}

		protected function getHandlerIdentifier (array $stubMethods, array $mockMethods = []):ModuleHandlerIdentifier {

			$identifier = $this->replaceConstructorArguments(

				ModuleHandlerIdentifier::class, [],

				array_merge([

					"getModules" => $this->modules,

					"handleGenericRequest" => $this->dummyRenderer
				], $stubMethods),

				$mockMethods,

				true, true, true, true
			);

			return $identifier;
		}
	}
?>
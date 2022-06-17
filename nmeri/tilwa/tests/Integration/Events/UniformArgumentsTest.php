<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\EmitterAsListener, Config\EventsMock};

	use InvalidArgumentException;

	class UniformArgumentsTest extends EventTestCreator {

		public function setUp ():void {}

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function(WriteOnlyContainer $container) {

				$container->replaceWithMock(Events::class, EventsMock::class, [

					"getManager" => EmitterAsListener::class
				]);
			});
		}

		public function test_cant_listen_on_emitter () {

			// given => see module injection

			$this->expectException(InvalidArgumentException::class);// then

			parent::setUp(); // when
		}
	}
?>
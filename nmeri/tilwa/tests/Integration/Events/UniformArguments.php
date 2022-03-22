<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\EmitterAsListener};

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use InvalidArgumentException;

	class UniformArgumentsTest extends TestEventManager {

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function(WriteOnlyContainer $container) {

				$container->replaceWithMock(Events::class, [

					"getManager" => EmitterAsListener::class
				]);
			});
		}

		public function test_cant_listen_on_emitter () {

			// given => see module injection

			$this->expectException(InvalidArgumentException::class);// then

			$this->getModuleFor(ModuleOne::class)

			->cascadeEntryEvent($this->payload); // when
		}
	}
?>
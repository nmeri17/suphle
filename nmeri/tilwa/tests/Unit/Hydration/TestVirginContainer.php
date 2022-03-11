<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\{Structures\BaseInterfaceCollection, DecoratorHydrator, InterfaceHydrator};

	use Tilwa\Contracts\Config\ModuleFiles;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\ModuleFilesMock;

	use PHPUnit\Framework\TestCase;

	class TestVirginContainer extends TestCase {

		use MockFacilitator;

		protected function bootContainer ($container):void {

			$container->initializeUniversalProvision();

			$container->provideSelf();

			$container->setExternalHydrator();
		}

		protected function stubDecorator () {

			return $this->positiveDouble(DecoratorHydrator::class, [

				"scopeArguments" => $this->returnArgument(1),

				"scopeInjecting" => $this->returnArgument(0)
			]);
		}

		protected function withDefaultInterfaceCollection ($container) {

			$container->setInterfaceHydrator(BaseInterfaceCollection::class);
		}

		protected function stubbedInterfaceCollection () {

			return $this->positiveDouble(InterfaceHydrator::class, [

				"deriveConcrete" => $this->returnCallback(function ($subject) {

					return $this->positiveDouble($subject, []);
				})
			]);
		}

		protected function registerCoreBindings ($container, array $bindings = []) {

			$container->whenTypeAny()->needsAny(array_merge([

				ModuleFiles::class => ModuleFilesMock::class
			], $bindings));
		}
	}
?>
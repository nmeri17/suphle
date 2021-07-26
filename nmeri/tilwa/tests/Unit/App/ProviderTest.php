<?php
	namespace Tilwa\Tests\Unit\App;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Contracts\HtmlParser;

	use Tilwa\Contracts\Config\{Services, ModuleFiles, Transphporm as ITransphporm};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{ServicesMock, ModuleFilesMock};

	use Tilwa\Config\Transphporm;

	class ProviderTest extends BaseTest {

		public function test_can_hydrate_from_provider () {

			$parser = HtmlParser::class;

			$this->assertInstanceOf($parser, $this->container->getClass($parser));
		}

		protected function containerConfigs ():array {

			return [

				Services::class => ServicesMock::class,

				ModuleFiles::class => ModuleFilesMock::class,

				ITransphporm::class => Transphporm::class
			];
		}
	}
?>
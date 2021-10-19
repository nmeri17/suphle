<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Contracts\Config\Services as ServicesContract;

	use Tilwa\Contracts\HtmlParser;

	use Tilwa\InterfaceLoader\HtmlTemplateProvider;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\InterfaceLoader\CProvider;

	class ServicesMock implements ServicesContract {

		public function lifecycle():bool {

			return false; // test probably wants this on
		}

		public function getLoaders():array {

			return [

				HtmlParser::class => HtmlTemplateProvider::class,

				CInterface::class => CProvider::class
			];
		}

		public function usesLaravelPackages ():bool {

			return true;
		}
	}
?>
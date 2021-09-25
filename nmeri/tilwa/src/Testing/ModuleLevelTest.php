<?php

	namespace Tilwa\Testing;

	use Tilwa\App\ModulesBooter;

	use PHPUnit\Framework\TestCase;

	abstract class ModuleLevelTest extends TestCase {

		protected function setUp ():void {

			(new ModulesBooter($this->getModules()))->prepare();
		}
		
		abstract protected function getModules():array;
	}
?>
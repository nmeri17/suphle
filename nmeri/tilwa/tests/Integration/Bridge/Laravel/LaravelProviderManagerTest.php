<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	/**
	 * @see [ProvidedServiceWrapper]
	*/
	class LaravelProviderManagerTest extends TestsConfig {

		public function test_can_invoke_helpers_only_in_wrapped () {

			$sut = $this->container->getClass(ConfigConstructor::class); // when

			$realSecondLevel = $this->getUnderlyingConfig()->get("nested.first_level.second_level");

			$this->assertSame($sut->getSecondLevel(), $realSecondLevel); // then
		}

		public function test_can_create_provider_with_helper () {

			$sut = $this->container->getClass(ConfigInternal::class); // when

			$realSecondLevel = $this->getUnderlyingConfig()->get("nested.first_level.second_level");

			// then
			$this->assertSame($sut->getSecondLevel(), $realSecondLevel); 

			$this->assertFalse(function_exists("config")); // didn't leak out
		}

		public function test_can_call_magic_method_on_target () {

			$sut = $this->container->getClass(ConfigInternal::class); // when

			$this->assertSame($sut->internalMagic(), $sut->magicValue()); // then
		}
	}
?>
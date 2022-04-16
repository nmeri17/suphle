<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\{AppConfig, NestedConfig};

	/**
	 * The idea demonstrated here is to compare results from the [RepositoryContract] given to laravel, and the one gotten after hydrating the object paired to that config i.e. [app => appOOP], we compare the results of [RepositoryContract] with directly calling [appOOP]
	*/
	class ConfigLoaderTest extends TestsConfig {

		public function test_their_config_can_get_ours () {

			$sut = $this->getUnderlyingConfig(); // when

			$ourConfig = new AppConfig($sut->get("app"));

			$newName = $ourConfig->name();

			$this->assertSame($newName, $sut->get("app.name")); // then
	    }

		public function test_will_receive_config_contents () {

			$sut = $this->getUnderlyingConfig(); // when

			$contents = $this->getNativeValues(NestedConfig::class);

			$this->assertSame($contents, $sut->get("nested")); // then
	    }

		public function test_can_get_nested_config_values () {

			$sut = $this->getUnderlyingConfig(); // when

			$value = $this->container->getClass(NestedConfig::class)->first_level()->second_level()["value"];

			$this->assertSame($value, $sut->get("nested.first_level.second_level.value")); // then
	    }

		public function test_fallsback_to_theirs_when_missing_property () {

			$sut = $this->getUnderlyingConfig(); // when

			$value = $this->getNativeValues(NestedConfig::class)["foo"];

			$this->assertSame($value, $sut->get("nested.foo")); // then
	    }

	    private function getNativeValues (string $className):array {

			return $this->container->getClass($className)

			->getNativeValues();
		}

		public function test_fallsback_to_theirs_when_missing_link () {

			$sut = $this->getUnderlyingConfig(); // when

			$value = "example.com";

			$this->assertSame($value, $sut->get("unavailable.link")); // then
	    }
	}
?>
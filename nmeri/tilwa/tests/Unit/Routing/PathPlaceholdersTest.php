<?php
	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Routing\PathPlaceholders;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	class PathPlaceholdersTest extends IsolatedComponentTest {

		use DirectHttpTest;

		/**
	     * @dataProvider pathsAndPatterns
	     */
		public function test_replaceInPattern (string $activePath, string $pattern) {

			$this->setHttpParams($activePath);

			$sut = $this->container->getClass(PathPlaceholders::class);

			$lowerCase = strtolower($sut->replaceInPattern($pattern));

			$this->assertSame($lowerCase, $activePath);
		}

		public function pathsAndPatterns ():array {

			return [
				["/segment-segment/5", "/SEGMENT-SEGMENT/id/"],
				["/segment/5", "/SEGMENT/id/"]
			];
		}
	}
?>
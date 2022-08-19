<?php
	namespace Suphle\Tests\Integration\Production;

	class TraditionalRequestsTest extends BaseTestProduction {
		
		/**
		 * @dataProvider modulesUrls
		*/
		public function test_can_visit_urls_in_test_env (string $url, string $output) {

			$this->get($url)->assertOk();
		}
		
		/**
		 * @dataProvider modulesUrls
		*/
		public function test_can_visit_urls_in_traditional_env (string $url, string $output) {

			$_GET["suphle_path"] = "/$url"; // given

			$indexPath = $this->fileSystemReader->getAbsolutePath(

				$this->binDir, "../../index.php"
			);

			$this->expectOutputString($output); // then

			require $indexPath; // when
		}
	}
?>
<?php
	namespace Suphle\Tests\Integration\Production;

	use Symfony\Component\Process;

	class RoadRunnerTest extends BaseTestProduction {
		
		/**
		 * @dataProvider modulesUrls
		*/
		public function test_can_visit_urls_after_server_setup (string $url, string $output) {

			$this->get($url)->assertOk(); // continue here
		}
	}
?>
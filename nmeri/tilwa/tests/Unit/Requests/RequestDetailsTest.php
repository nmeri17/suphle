<?php
	namespace Tilwa\Tests\Unit\Requests;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Routing\RequestDetails;

	class RequestDetailsTest extends BaseTest {

		public function test_mock_request_populates_superglobal () {

			$path = "/hello";

			$this->setHttpParams($path);

			$mockConfig = $this->createMock(Router::class);

			$requestDetails = new RequestDetails($mockConfig);

			$this->assertEquals($path, $requestDetails->getPath());
		}
	}
?>
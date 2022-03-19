<?php
	namespace Tilwa\Tests\Unit\Requests;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Request\RequestDetails;

	class RequestDetailsTest extends IsolatedComponentTest {

		use DirectHttpTest;

		public function test_mock_request_populates_superglobal () {

			$path = "/hello";

			$this->setHttpParams($path);

			$mockConfig = $this->createMock(Router::class);

			$requestDetails = new RequestDetails($mockConfig);

			$this->assertEquals($path, $requestDetails->getPath());
		}
	}
?>
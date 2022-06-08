<?php
	namespace Tilwa\Tests\Unit\Requests;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class RequestDetailsTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		public function test_mock_request_populates_superglobal () {

			$path = "/hello";

			$this->setHttpParams($path);

			$mockConfig = $this->createMock(Router::class);

			$requestDetails = new RequestDetails($mockConfig);

			$this->assertEquals($path, $requestDetails->getPath());
		}
	}
?>
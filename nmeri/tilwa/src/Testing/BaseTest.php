<?php

	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel, Router as IRouter, Auth as IAuth, Transphporm as ITransphporm, ModuleFiles as IModuleFiles};

	use Tilwa\Contracts\Auth\UserHydrator as IUserHydrator;

	use Tilwa\Config\{ Services, Laravel, Auth, Transphporm}; // using our default config for these

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, ModuleFilesMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	use Tilwa\Tests\Mocks\Auth\ArrayUserHydratorMock;

	use PHPUnit\Framework\TestCase;

	class BaseTest extends TestCase { // rename this to IsolatedComponentTest

		const JSON_HEADER_VALUE = "application/json";

		const HTML_HEADER_VALUE = "application/x-www-form-urlencoded";

		protected $container;

		protected function setUp ():void {

			$this->container = new Container;

			$this->bootContainer()->bindEntities();
		}

		protected function bootContainer ():self {

			$this->container->setConfigs($this->containerConfigs());

			return $this;
		}

		protected function bindEntities ():self {

			$this->container->whenTypeAny()->needsAny([

				IUserHydrator::class => new ArrayUserHydratorMock,

				Container::class => $this->container,

				IRouter::class => new RouterMock(BrowserNoPrefix::class)
			]);

			return $this;
		}

		protected function containerConfigs ():array {

			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class,

				IAuth::class => Auth::class,

				ITransphporm::class => Transphporm::class,

				IModuleFiles::class => ModuleFilesMock::class
			];
		}

		/**
		 * Writes to the superglobals RequestDetails can read from but doesn't actually send any request. Use when we're invoking router/request handler directly
		*/
		protected function setHttpParams (string $requestPath, string $httpMethod = "get", string $payload = "", array $headers = []):void {

			$_GET["tilwa_path"] = $requestPath;

			$_SERVER["REQUEST_METHOD"] = $httpMethod;

			$_SERVER += $headers;

			$this->writePayload($payload, $headers["Content-Type"]);
		}

		protected function sendJsonPayload (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$contentType = ["Content-Type" => self::JSON_HEADER_VALUE];

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, json_encode($payload), $headers);

				return true;
			}

			return false;
		}

		protected function sendHtmlForm (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$headers = ["Content-Type" => self::HTML_HEADER_VALUE];

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, http_build_query($payload), $headers);

				return true;
			}

			return false;
		}

		private function isValidPayloadType (string $httpMethod):bool {

			return in_array($httpMethod, ["post", "put"]);
		}

		private function writePayload (string $payload, string $contentType):void {

			if ($contentType != self::JSON_HEADER_VALUE)

				$_POST = $payload;

			else file_put_contents("php://output", $payload);
		}
	}
?>
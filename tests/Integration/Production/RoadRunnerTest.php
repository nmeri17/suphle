<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\Tests\Mocks\Modules\ModuleOne\OutgoingRequests\VisitSegment;

	use Symfony\Component\Process\Process;

	use GuzzleHttp\Exception\RequestException;

	use Psr\Http\Message\ResponseInterface;

	use Throwable;

	class RoadRunnerTest extends BaseTestProduction {

		private $requestSender = VisitSegment::class;
		
		/**
		 * @dataProvider modulesUrls
		*/
		public function test_can_visit_urls_after_server_setup (string $url, string $expectedOutput) {

			$this->sendRequestToProcess($url, $expectedOutput);
		}

		protected function sendRequestToProcess (string $url, string $expectedOutput):void {

			$this->ensureExecutableRuns();

			$configPath = $this->fileSystemReader->getAbsolutePath(

				$this->binDir, "../../test-rr.yaml"
			);

			$serverProcess = new Process([

				$this->binDir ."rr", "serve", "-c", $configPath
			]);

			$serverProcess->setTimeout(20_000);

			try {

				$serverProcess->start();

				if (!$this->serverIsReady($serverProcess))

					$this->fail(

						"Unable to start server:". "\n".

						$this->processFullOutput($serverProcess)
					);

				$parameters = $this->getContainer()

				->getMethodParameters(Container::CLASS_CONSTRUCTOR, $this->requestSender);

				$httpService = $this->replaceConstructorArguments(

					$this->requestSender, $parameters, [

						"getRequestUrl" => $url
					]
				);

				$response = $httpService->getDomainObject();

				if ($httpService->hasErrors()) {

					$exception = $httpService->getException();

					var_dump(

						$this->getResponseBody($exception->getResponse()),

						$this->processFullOutput($serverProcess)
					);

					$this->fail($exception);
				}

				$this->assertSame(200, $response->getStatusCode());

				$this->assertSame(

					$expectedOutput,

					$this->getResponseBody($response)
				);
			}
			finally {

				$serverProcess->stop();
			}
		}

		private function ensureExecutableRuns ():void {

			$helpProcess = new Process([ $this->binDir. "rr", "--help" ] );

			$helpProcess->mustRun();

			$this->assertTrue($helpProcess->isSuccessful());

			$this->assertStringContainsStringIgnoringCase(

				"available commands", $helpProcess->getOutput()
			);
		}

		private function serverIsReady (Process $serverProcess):bool {

			$serverProcess->waitUntil(function ($type, $buffer) {

				return stripos($buffer, "http server was started");
			});

			return $serverProcess->isRunning();
		}

		private function getResponseBody (ResponseInterface $response) {

			return $response->getBody()->getContents();
		}

		private function processFullOutput (Process $process):string {

			return $process->getOutput() . "\n".

			$process->getErrorOutput();
		}

		/**
		 * @dataProvider moduleThreeUrls
		*/
		public function test_controller_action_can_read_different_ids (string $url, string $expectedOutput) {

			$this->sendRequestToProcess($url, $expectedOutput);
		}
	}
?>
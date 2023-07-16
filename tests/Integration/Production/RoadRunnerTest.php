<?php

namespace Suphle\Tests\Integration\Production;

use Suphle\Hydration\Container;

use Suphle\Server\VendorBin;

use Suphle\Testing\Utilities\PingHttpServer;

use Suphle\Tests\Mocks\Modules\ModuleOne\OutgoingRequests\VisitSegment;

use GuzzleHttp\Exception\RequestException;

use Psr\Http\Message\ResponseInterface;

use Symfony\Component\Process\Process;

use Throwable;

class RoadRunnerTest extends BaseTestProduction
{
    use PingHttpServer;

    protected const REQUEST_SENDER = VisitSegment::class,

    SERVER_TIMEOUT = 2850, // stop process if unable to start server after these seconds

    RR_CONFIG = "../../test-rr.yaml";

    /**
     * @dataProvider modulesUrls
    */
    public function test_can_visit_urls_after_server_setup(string $url, string $expectedOutput)
    {

        $this->sendRequestToProcess($url, $expectedOutput);
    }

    protected function sendRequestToProcess(string $url, string $expectedOutput): void
    {

        $this->ensureExecutableRuns();

        $serverProcess = $this->vendorBin->getServerLauncher(self::RR_CONFIG);

        $serverProcess->setTimeout(self::SERVER_TIMEOUT);

        try {

            $serverProcess->start();

            if (!$this->serverIsReady($serverProcess)) {

                $this->fail(
                    "Unable to start server:\n".

                    $this->processFullOutput($serverProcess)
                );
            }

            $response = $this->launchIncursiveRequest($url); // when

            $this->assertSame(200, $response->getStatusCode());

            $this->assertSame(
                $expectedOutput,
                $this->getResponseBody($response)
            );
        } finally {

            $serverProcess->stop();
        }
    }

    protected function launchIncursiveRequest (string $url):ResponseInterface {

    	$parameters = $this->getContainer()

        ->getMethodParameters(Container::CLASS_CONSTRUCTOR, self::REQUEST_SENDER);

        $httpService = $this->replaceConstructorArguments(
            self::REQUEST_SENDER,
            $parameters,
            [

                "getRequestUrl" => "localhost:8080/$url"
            ]
        );

        if ($httpService->hasErrors()) {

            $exception = $httpService->getException();

            var_dump($this->processFullOutput($serverProcess));

            var_dump($this->getResponseBody( // comes after the above so even if this fails, we can have an idea of what went wrong

                $exception->getResponse()
            ));

            $this->fail($exception);
        }

        return $httpService->getDomainObject();
    }

    private function ensureExecutableRuns(): void
    {

        $helpProcess = $this->vendorBin->setProcessArguments(VendorBin::RR_BINARY, ["--help" ]);

        $helpProcess->mustRun();

        $this->assertTrue($helpProcess->isSuccessful());

        $this->assertStringContainsStringIgnoringCase(
            "available commands",
            $helpProcess->getOutput()
        );
    }

    private function getResponseBody(ResponseInterface $response)
    {

        return $response->getBody()->getContents();
    }

    /**
     * @dataProvider moduleThreeUrls
    */
    public function test_controller_action_can_read_different_ids(string $url, string $expectedOutput)
    {

        $this->sendRequestToProcess($url, $expectedOutput);
    }

    public function test_single_process_can_handle_multiple_requests()
    {

        $serverProcess = $this->vendorBin->getServerLauncher(self::RR_CONFIG);

        $serverProcess->setTimeout(self::SERVER_TIMEOUT);

        try {

            $serverProcess->start();

            if (!$this->serverIsReady($serverProcess)) {

                $this->fail(
                    "Unable to start server:\n".

                    $this->processFullOutput($serverProcess)
                );
            }

            foreach ($this->modulesUrls() as $dataSet) {

                $response = $this->launchIncursiveRequest($dataSet[0]); // when

                $responseBody = $this->getResponseBody($response);

                $expectedOutput = $dataSet[1];

                // var_dump(205, $url/*, $responseBody, $expectedOutput*/);

                if ($expectedOutput != $responseBody) {

                    var_dump($this->processFullOutput($serverProcess));
                }

                $this->assertSame($expectedOutput, $responseBody);
            }
        } finally {

            $serverProcess->stop();
        }
    }
}

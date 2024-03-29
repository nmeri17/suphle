<?php

namespace Suphle\Server;

use Suphle\Modules\ModuleHandlerIdentifier;

use Suphle\Contracts\{Presentation\BaseRenderer, Queues\Adapter as QueueAdapter};

use Spiral\RoadRunner\{Worker, Http\PSR7Worker};

use Nyholm\Psr7\Factory\Psr17Factory;

use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

use Throwable;

/**
 * RoadRunner will spin this up multiple times for each worker it has to create to service a request type
*/
class ModuleWorkerAccessor
{
    private PSR7Worker $httpWorker;

    protected QueueAdapter $queueWorker;

    public function __construct(
        protected readonly ModuleHandlerIdentifier $handlerIdentifier,
        protected readonly bool $isHttpMode
    ) {

        //
    }

    public function runInSandbox(callable $serverAction, callable $onServerError = null): void
    {

        try {

            $serverAction($this);
        } catch (Throwable $exception) {

            if (!is_null($onServerError)) {

                $onServerError($exception);
            }

            $worker = $this->getHttpWorker();

            $worker->waitRequest(); // to get headers

            $worker->getWorker()->error($exception->getMessage());
        }
    }

    public function buildIdentifier(): self
    {

        $this->handlerIdentifier->bootModules();

        return $this;
    }

    public function setActiveWorker(): self
    {

        if ($this->isHttpMode) {

            $this->httpWorker = $this->getHttpWorker();
        } else {
            $this->queueWorker = $this->getQueueWorker();
        }

        return $this;
    }

    protected function getHttpWorker(): PSR7Worker
    {

        $psrFactory = new Psr17Factory();

        return new PSR7Worker(
            Worker::create(),
            $psrFactory,
            $psrFactory,
            $psrFactory
        );
    }

    public function getQueueWorker(): QueueAdapter
    {

        return $this->handlerIdentifier

        ->firstContainer()->getClass(QueueAdapter::class);
    }

    /**
     * It's only safe to start outputing things from this point, after workers have been setup
    */
    public function openEventLoop(): void
    {

        if ($this->isHttpMode) {
            $this->processHttpTasks();
        } else {
            $this->queueWorker->processTasks();
        }
    }

    protected function processHttpTasks(): void
    {

        while ($newRequest = $this->httpWorker->waitRequest()) {

            try {

                $this->flushHttpResponse($newRequest);
            } catch (Throwable $exception) { // only roadRunner specific errors are expected here, since our own errors are fully handled internally

                $this->httpWorker->getWorker()->error(
                    $exception->getMessage(). "\n".

                    $exception->getTraceAsString()
                );
            }
        }
    }

    protected function flushHttpResponse(?ServerRequestInterface $newRequest): void
    {

        $renderer = $this->getRequestRenderer(
            $newRequest->getRequestTarget(),
            
            false, $newRequest
        );

        $this->httpWorker->respond(
            $this->getPsrResponse($renderer)
        );
    }

    public function getRequestRenderer(string $urlPattern, bool $outputHeaders, ServerRequestInterface $contextualRequest): BaseRenderer
    {

        $this->handlerIdentifier->setRequestPath($urlPattern, null, $contextualRequest);

        $this->handlerIdentifier->diffuseSetResponse($outputHeaders);

        return $this->handlerIdentifier->underlyingRenderer();
    }

    protected function getPsrResponse(BaseRenderer $renderer): ResponseInterface
    {

        $symfonyResponse = new SymfonyResponse(
            $renderer->render(),
            $renderer->getStatusCode(),
            $renderer->getHeaders()
        );

        $psr17Factory = new Psr17Factory();

        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        return $psrHttpFactory->createResponse($symfonyResponse);
    }

    public function safeSetupWorker(): void
    {

        $this->runInSandbox(function ($accessor) {

            $this->buildIdentifier()->setActiveWorker()

            ->openEventLoop();
        }, function (Throwable $exception): never {

            var_dump("Failing worker alert", $exception); // if we get here, it means loop terminated/request failed and rr is restarting another one for us

            throw $exception;
        });
    }
}

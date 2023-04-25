<?php

namespace Suphle\Modules;

use Suphle\Hydration\{Container, DecoratorHydrator, Structures\ObjectDetails};

use Suphle\Request\PayloadStorage;

use Suphle\Exception\DetectedExceptionManager;

use Suphle\Contracts\{Modules\HighLevelRequestHandler, Config\ExceptionInterceptor, Presentation\BaseRenderer, Hydration\ClassHydrationBehavior};

use Suphle\Contracts\Exception\{FatalShutdownAlert, ExceptionHandler};

use Throwable;
use Exception;

class ModuleExceptionBridge implements HighLevelRequestHandler, ClassHydrationBehavior
{
    protected ExceptionHandler $handler;

    protected bool $handledExternally = false;

    public function __construct(
        protected readonly Container $container,
        protected readonly ExceptionInterceptor $config,
        protected readonly PayloadStorage $payloadStorage,
        protected readonly DetectedExceptionManager $exceptionDetector,
        protected readonly DecoratorHydrator $decoratorHydrator,
        protected readonly ObjectDetails $objectMeta
    ) {

        //
    }

    public function hydrateHandler(Throwable $exception): void
    {

        $handlers = $this->config->getHandlers();

        $exceptionName = $exception::class;

        if (array_key_exists($exceptionName, $handlers)) {

            $handlerName = $handlers[$exceptionName];
        } else {
            $handlerName = $this->exceptionFromParent($exceptionName, $handlers);
        }

        $this->handler = $this->container->getClass($handlerName);

        $this->handler->setContextualData($exception);
    }

    /**
     * Using this so exceptions can be stubbed and still caught by the bound handler
    */
    protected function exceptionFromParent(string $exceptionName, array $handlers): string
    {

        foreach ($handlers as $exceptionParent => $handlerName) {

            if ($this->objectMeta->stringInClassTree($exceptionName, $exceptionParent)) {

                return $handlerName;
            }
        }

        return $this->config->defaultHandler();
    }

    public function handlingRenderer(): ?BaseRenderer
    {

        $this->handler->prepareRendererData();

        $this->handledExternally = true; // Causes it not to send out alerts except for uncatchable errors

        $renderer = $this->handler->getRenderer();

        return $this->decoratorHydrator->scopeInjecting(
            $renderer,
            self::class
        );
    }

    /**
     * That this works correctly is untestable (after ModuleHandlerIdentifier::findExceptionRenderer fails)
    */
    public function epilogue(): void
    {

        register_shutdown_function(function () {

            echo $this->shutdownRites();
        });
    }

    public function shutdownRites(): ?string
    {

        $lastError = error_get_last();

        if ($this->isFalsePositive($lastError) || $this->handledExternally) {

            return null;
        } // no error. Just end of request

        $stringifiedError = json_encode($lastError, JSON_PRETTY_PRINT);

        try {

            return $this->gracefulShutdown($stringifiedError);
        } catch (Throwable $exception) {

            return $this->disgracefulShutdown($stringifiedError, $exception);
        }
    }

    protected function isFalsePositive(?array $errorDetails): bool
    {

        return is_null($errorDetails) ||

        in_array($errorDetails["type"], [

            E_NOTICE, E_USER_NOTICE, E_USER_WARNING, E_WARNING
        ]);
    }

    /**
     * The one place we never wanna be
    */
    public function disgracefulShutdown(string $errorDetails, Throwable $exception): string
    {

        $errorDetails .= \Wyrihaximus\throwable_json_encode($exception); // regular json_encode can't serialize throwables

        file_put_contents($this->config->shutdownLog(), $errorDetails, FILE_APPEND);

        $this->writeStatusCode(500);

        $alerter = $this->container->getClass(FatalShutdownAlert::class);

        $alerter->setErrorAsJson($errorDetails);

        $alerter->handle();

        return $this->config->shutdownText();
    }

    public function gracefulShutdown(string $errorDetails): string
    {

        $this->handler = $this->container->getClass($this->config->defaultHandler());

        $exception = new Exception($errorDetails); // this means this will have a fake trace

        $this->handler->setContextualData($exception);

        $this->exceptionDetector->queueAlertAdapter($exception, $this->payloadStorage);

        $renderer = $this->decoratorHydrator->scopeInjecting(
            $this->handlingRenderer(),
            self::class
        );

        $this->writeStatusCode($renderer->getStatusCode());

        return $renderer->render();
    }

    public function writeStatusCode(int $statusCode): void
    {

        http_response_code($statusCode);
    }

    public function protectRefreshPurge(): bool
    {

        return true; // in tests, this is provided before PayloadStorage, which is one of its dependencies
    }
}

<?php
namespace Suphle\Middleware\Handlers;

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Contracts\{Requests\ValidationEvaluator, Presentation\BaseRenderer};

use Suphle\Request\PayloadStorage;

class ValidationRulesMiddleware extends BaseMiddleware {

    public function __construct(
        protected readonly ValidationEvaluator $rulesHandler
    ) {}        

    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer {

        if (!$this->rulesHandler->eligibleToValidate())

            return $requestHandler->handle($payloadStorage);

        $this->rulesHandler->executeValidatorManager();

        return $requestHandler->handle($payloadStorage);
    }
}
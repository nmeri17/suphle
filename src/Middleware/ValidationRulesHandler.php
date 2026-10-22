<?php
namespace Suphle\Middleware;

use Suphle\Hydration\{Container, Structures\CallbackDetails};

use Suphle\Contracts\{Requests\ValidationEvaluator, Presentation\BaseRenderer, IO\Session, Response\RendererManager};

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Request\{PayloadStorage, RequestDetails, ValidatorManager};

use Suphle\Services\Decorators\ValidationRules; 

use Suphle\Exception\Explosives\{ValidationFailure, DevError\NoCompatibleValidator};

class ValidationRulesHandler implements ValidationEvaluator {

    protected ValidatorManager $validatorManager;

    public function __construct(
        protected readonly CallbackDetails $callbackDetails, 
        protected readonly RequestDetails $requestDetails, 
        protected readonly Container $container,
        protected readonly Session $sessionClient,
        protected readonly RouteInfo $routeDetails,
        protected readonly RendererManager $rendererManager
    ) {} 

    /**
     * {@inheritdoc}
    */
    public function executeValidatorManager():void {

        $this->validatorManager = $this->container->getClass(ValidatorManager::class); // lazily setting this since it incurs a ton of hydration irrelevant to every request

        $validators = $this->getValidationAttributes();

        $this->validatorManager->setActionRules(
            end($validators)->newInstance()->rules // use only the latest
        );

        if (!$this->validatorManager->isValidated())

            throw new ValidationFailure($this);

        $this->sessionClient->resetOldInput(); // discard any validation errors
    }

    protected function getValidationAttributes ():array {

        $coodinator = $this->routeDetails->controllerClass;

        $handlingMethod = $this->routeDetails->controllerMethod;

        return $this->callbackDetails->getMethodAttributes(

            $coodinator, $handlingMethod, ValidationRules::class
        );
    }

    /**
     * {@inheritdoc}
    */
    public function eligibleToValidate(): bool {

        if (!empty($this->getValidationAttributes())) {
            return true;
        }

        if ($this->requestDetails->isGetRequest()) {
            return false;
        }

        throw new NoCompatibleValidator(
            $this->routeDetails->controllerClass,
            $this->routeDetails->controllerMethod
        );
    }

    public function getValidatorErrors(): array
    {

        return $this->validatorManager->validationErrors();
    }

    public function validationRenderer(array $failureDetails): BaseRenderer
    {

        return $this->rendererManager->invokePreviousRenderer($failureDetails);
    }
}
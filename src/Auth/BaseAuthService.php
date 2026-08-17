<?php
namespace Suphle\Auth;

use Suphle\Contracts\Auth\{AuthStorage, ColumnPayloadComparer};

use Suphle\Auth\Storage\{TokenStorage, SessionStorage};

use Suphle\Contracts\Config\Auth as AuthConfig;

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};

use Suphle\Services\Structures\BaseErrorCatcherService;

#[InterceptsCalls]
#[VariableDependencies(["setPayloadStorage"])]
#[DomainService]
class BaseAuthService {

    use BaseErrorCatcherService;

    public function __construct(
        protected readonly ColumnPayloadComparer $comparer,
        protected readonly SessionStorage $sessionStorage,
        protected readonly TokenStorage $tokenStorage,
        protected readonly AuthConfig $authConfig
    ) {}

    public function tryGetJsonToken():?string {

        return $this->compareCredentials($this->tokenStorage);
    }

    public function tryStartUserSession():?string {

        return $this->compareCredentials($this->sessionStorage);
    }

    protected function compareCredentials(AuthStorage $storage):?string {

        if ($this->comparer->compare())

            return $storage->startSession($this->comparer->getUser()->getId());
        
        return null;
    }

    public function successRedirect(string $destination = "/"):callable {
        
        return function (PayloadStorage $payloadStorage) use ($destination) {

            if (!$payloadStorage->hasKey("path")) return $destination;

            $path = $payloadStorage->getKey("path");
            
            $queryPart = $payloadStorage->getKey("query");
            
            if (!empty($queryPart)) {
                $path .= "?" . $queryPart;
            }
            return $path;
        };
    }

    // wrapper since configs can't be injected on coordinator side
    public function authRequiredUrl ():string {

        return $this->authConfig->markupRedirect();
    }
}
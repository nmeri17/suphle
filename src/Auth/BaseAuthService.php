<?php
namespace Suphle\Auth;

use Suphle\Contracts\Auth\{AuthStorage, ColumnPayloadComparer};

use Suphle\Auth\Storage\{TokenStorage, SessionStorage};

use Suphle\Contracts\Config\Auth as AuthConfig;

class BaseAuthService {

    public function __construct(
        protected readonly ColumnPayloadComparer $comparer,
        protected readonly SessionStorage $sessionStorage,
        protected readonly TokenStorage $tokenStorage
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

    public function authRequiredUrl ():string {

        return function (AuthConfig $config) {

            return $config->mark
        };
    }
}
<?php

namespace Suphle\Services;

use Suphle\Services\Decorators\SecuresPostRequest;

use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

use Suphle\Contracts\IO\Session;

#[SecuresPostRequest]
class ServiceCoordinator
{

    public function __construct(protected readonly Session $sessionClient) {

        //
    }

    protected function copyValidationErrors(array $payload): array
    {

        if ($this->sessionClient->hasOldInput(ValidationFailureDiffuser::ERRORS_PRESENCE)) {

            foreach (ValidationFailureDiffuser::FAILURE_KEYS as $key) {

                $payload[$key] = $this->sessionClient->getOldInput($key);
            }
        }

        return $payload;
    }
}

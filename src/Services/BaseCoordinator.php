<?php
namespace Suphle\Services;

use Suphle\Request\RequestDetails;

use Suphle\Services\Decorators\SecuresPostRequest;

use Suphle\Contracts\IO\Session;

use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

#[SecuresPostRequest] // this is what activates sprh in the first place
abstract class BaseCoordinator
{
    public function __construct(
        protected readonly RequestDetails $requestDetails,
        protected readonly Session $sessionClient
    ) {
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
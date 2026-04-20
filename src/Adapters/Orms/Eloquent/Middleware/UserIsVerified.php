<?php
namespace Suphle\Adapters\Orms\Eloquent\Middleware;

use Suphle\Middleware\{BaseMiddleware, MiddlewareNexts};

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\{Presentation\BaseRenderer, Auth\AuthStorage};

use Suphle\Exception\Explosives\UnverifiedAccount;

class UserIsVerified extends BaseMiddleware
{
    public function __construct(
        protected readonly AuthStorage $authStorage
    ) {}
    /**
     * @throws UnverifiedAccount
     */
    public function process(
        PayloadStorage $payloadStorage, 
        ?MiddlewareNexts $requestHandler
    ): BaseRenderer {

        $defaults = [
            "verification_url" => "/accounts/verify",

            "verified_field" => "email_verified_at"
        ];

        foreach ($defaults as $key => $value)

            if (array_key_exists($key, $this->args))

                $defaults[$key] = $this->args[$key];

        $columnName = $defaults["verified_field"];

        if (is_null($this->authStorage->getUser()->$columnName)) {

            throw new UnverifiedAccount($defaults["verification_url"]);
        }

        return $requestHandler->handle($payloadStorage);
    }
}

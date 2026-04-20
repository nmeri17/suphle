<?php
namespace Suphle\Config;

use Suphle\Contracts\Config\Auth as AuthConfig;

class Auth implements AuthConfig {

    public function getModelObservers(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
    */
    public function markupRedirect ():string {

        return "/auth/login";
    }
}

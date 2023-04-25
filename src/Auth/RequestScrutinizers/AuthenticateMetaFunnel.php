<?php

namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Contracts\Auth\AuthStorage;

use Suphle\Routing\CollectionMetaFunnel;

class AuthenticateMetaFunnel extends CollectionMetaFunnel
{
    public function __construct(
        protected readonly array $activePatterns,
        public readonly AuthStorage $authStorage
    ) {

        //
    }
}

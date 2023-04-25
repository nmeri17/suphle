<?php

namespace Suphle\Adapters\Orms\Eloquent\RequestScrutinizers;

use Suphle\Routing\CollectionMetaFunnel;

class AccountVerifiedFunnel extends CollectionMetaFunnel
{
    public function __construct(
        protected readonly array $activePatterns,
        public readonly string $verificationUrl = "/accounts/verify",
        public readonly string $verificationColumn = "email_verified_at"
    ) {

        //
    }
}

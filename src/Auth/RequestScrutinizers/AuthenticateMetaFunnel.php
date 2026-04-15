<?php
namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Contracts\Auth\AuthStorage;

class AuthenticateMetaFunnel
{
    /**
     * The framework injects the specific mechanism (Session, Token, etc.) 
     * decided during the "Implicit Swap" phase.
     */
    public function __construct(
        public readonly AuthStorage $authStorage
    ) {}
}
<?php
namespace Suphle\Auth\RequestScrutinizers;

class AuthorizeMetaFunnel
{
    /**
     * @param string $ruleClass FQN of a class extending RouteRule
     */
    public function __construct(
        public readonly string $ruleClass
    ) {}
}
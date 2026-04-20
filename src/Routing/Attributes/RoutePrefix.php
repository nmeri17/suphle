<?php
namespace Suphle\Routing\Attributes;

use Suphle\Auth\Storage\TokenStorage;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RoutePrefix
{
    public function __construct(
        public readonly string $prefix, // Mandatory: "posts", "users", or ""
        public readonly ?string $mirrorPrefix = null, // "api/v1"
        public readonly ?string $mirrorAuthenticator = TokenStorage::class,
        public readonly string $mirrorHeader = "application/json",
        public readonly array $excludeMethods = [] // works with mirror ie don't mirror if child routes are present
    ) {}
}
<?php
namespace Suphle\Routing\Attributes;

use Suphle\Auth\Storage\TokenStorage;

use Suphle\Request\PayloadStorage;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RoutePrefix
{
    public function __construct(
        public readonly string $prefix, // "posts", "/users", "/"
        public readonly ?string $mirrorPrefix = null, // "api/v1"
        public readonly string $mirrorAuthenticator = TokenStorage::class,
        public readonly array $mirrorHeader = [
            
            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
        ],
        public readonly array $excludeMethods = [], // works with mirror ie don't mirror if child routes are present
    ) {}
}
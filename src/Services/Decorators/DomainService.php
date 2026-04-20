<?php
namespace Suphle\Services\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class DomainService {

    public function __construct(
        public readonly bool $mutation = false
    ) {}
}
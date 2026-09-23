<?php
namespace Suphle\Services\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidationRules
{
    public function __construct(public readonly array $rules)
    {}
}

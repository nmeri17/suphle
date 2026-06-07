<?php
namespace Suphle\Contracts;

use Psalm\Internal\Codebase\Methods;

interface PsalmCodebase {

    public function getMethodAnalyzer():Methods;
}
<?php

namespace Suphle\Contracts\Hydration;

interface ExternalPackageManager
{
    public function canProvide(string $fullName): bool;

    /**
     * @return Instance of requested argument
    */
    public function manageService(string $fullName);
}

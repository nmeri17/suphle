<?php

namespace Suphle\Contracts\Config;

interface Router extends ConfigMarker {

    /**
     * List in ascending order of execution
     */
    public function defaultMiddleware(): array;

    /**
     * Get the relative path to coordinator directories within modules
     * Default should be "Coordinators" for the new attribute-based system
     */
    public function getCoordinatorPath(): string;

    public function getWebSocketPath(): string;

    /**
     * Get specific coordinator directories to scan for routes
     * This allows tests to isolate specific coordinators and prevents scanning undesired ones
     * 
     * @return string[] Array of coordinator class names to scan, or empty array to scan all
     *                  Example: ['HomeCoordinator', 'UserCoordinator'] or [] for all
     */
    public function getCoordinatorClassesToScan(): array;
}

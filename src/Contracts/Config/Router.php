<?php

namespace Suphle\Contracts\Config;

interface Router extends ConfigMarker
{
    public function apiPrefix(): string;

    /**
     * List in ascending order of execution
     */
    public function defaultMiddleware(): array;

    /**
     * @return [CollectionMetaFunnel => CollectibleMiddlewareHandler]
     */
    public function collectorHandlers(): array;

    /**
     * @return [CollectionMetaFunnel => BaseScrutinizerHandler]
     */
    public function scrutinizerHandlers(): array;

    public function mirrorsCollections(): bool;

    // names the storage mechanism to be used on the browser collection when we've switched to those collections
    // This is still needed for browser-based AJAX requests that use session cookies instead of bearer tokens
    public function mirrorAuthenticator(): string;

    /**
     * Get the relative path to coordinator directories within modules
     * Default should be "Coordinators" for the new attribute-based system
     */
    public function getCoordinatorPath(): string;

    /**
     * Get specific coordinator directories to scan for routes
     * This allows tests to isolate specific coordinators and prevents scanning undesired ones
     * 
     * @return string[] Array of coordinator class names to scan, or empty array to scan all
     *                  Example: ['HomeCoordinator', 'UserCoordinator'] or [] for all
     */
    public function getCoordinatorClassesToScan(): array;
}

<?php

namespace Isaacjuwon\LaravelTenancy\Contracts;

interface Tenant
{
    /**
     * Use this tenant as the active tenant.
     */
    public function use(): void;

    /**
     * Forget the current active tenant.
     */
    public function forget(): void;

    /**
     * Run the given callback within this tenant's context.
     */
    public function run(callable $callback): mixed;

    /**
     * Resolve the currently active tenant, if any.
     */
    public static function current(): ?static;
}

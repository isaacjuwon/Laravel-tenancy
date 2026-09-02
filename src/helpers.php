<?php

use Isaacjuwon\LaravelTenancy\Contracts\Tenant;

if (! function_exists('tenant')) {
    /**
     * Resolve the currently active tenant, if any.
     */
    function tenant(): ?Tenant
    {
        return app()->has('tenant') ? app('tenant') : null;
    }
}

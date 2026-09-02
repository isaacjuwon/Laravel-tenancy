<?php

namespace Isaacjuwon\LaravelTenancy\Listeners;

use Illuminate\Container\Attributes\Singleton;
use Isaacjuwon\LaravelTenancy\Events\ForgettingTenant;
use Isaacjuwon\LaravelTenancy\Events\UsingTenant;

#[Singleton]
class ConfigureTenant
{
    /**
     * The application config values captured before any tenant took over,
     * so they can be restored once the tenant is forgotten.
     */
    protected array $original = [];

    public function __construct()
    {
        $this->original = config()->get(['mail.from.name']);
    }

    /**
     * Apply tenant-specific configuration when a tenant becomes active.
     * Customize this to swap whatever config values your app needs to
     * scope per tenant (mail sender, filesystem disk, cache prefix, etc).
     */
    public function handleUsingTenant(UsingTenant $event): void
    {
        config()->set([
            'mail.from.name' => $event->tenant->name.' (via '.config('app.name').')',
        ]);
    }

    /**
     * Restore the original configuration once the tenant is forgotten.
     */
    public function handleForgettingTenant(ForgettingTenant $event): void
    {
        config()->set($this->original);
    }
}

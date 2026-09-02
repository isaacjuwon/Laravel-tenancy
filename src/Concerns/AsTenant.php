<?php

namespace Isaacjuwon\LaravelTenancy\Concerns;

use Illuminate\Support\Facades\Context;
use Isaacjuwon\LaravelTenancy\Events\ForgettingTenant;
use Isaacjuwon\LaravelTenancy\Events\UsingTenant;

trait AsTenant
{
    /**
     * Use this tenant as the active tenant.
     */
    public function use(): void
    {
        UsingTenant::dispatch($this);

        app()->instance('tenant', $this);

        Context::add(config('tenancy.context_key', 'tenantId'), $this->getKey());
    }

    /**
     * Forget the current active tenant.
     */
    public function forget(): void
    {
        ForgettingTenant::dispatch($this);

        app()->forgetInstance('tenant');

        Context::forget(config('tenancy.context_key', 'tenantId'));
    }

    /**
     * Run the given callback within this tenant's context, then restore
     * whichever tenant (if any) was active beforehand.
     */
    public function run(callable $callback): mixed
    {
        $original = static::current();

        $this->use();

        return tap($callback($this), function () use ($original) {
            $original ? $original->use() : $this->forget();
        });
    }

    /**
     * Resolve the currently active tenant, if any.
     */
    public static function current(): ?static
    {
        return app()->has('tenant') ? app('tenant') : null;
    }
}

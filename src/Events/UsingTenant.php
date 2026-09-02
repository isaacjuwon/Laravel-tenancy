<?php

namespace Isaacjuwon\LaravelTenancy\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Isaacjuwon\LaravelTenancy\Contracts\Tenant;

class UsingTenant
{
    use Dispatchable;

    public function __construct(
        public readonly Tenant $tenant,
    ) {}
}

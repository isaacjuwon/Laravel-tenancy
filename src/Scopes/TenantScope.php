<?php

namespace Isaacjuwon\LaravelTenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->when(tenant())->where(function (Builder $builder) {
            $builder->whereBelongsTo(tenant(), 'tenant');
        });
    }

    public function extend(Builder $builder): void
    {
        $builder->macro(
            'withoutTenancy',
            fn (Builder $builder) => $builder->withoutGlobalScope($this)
        );
    }
}

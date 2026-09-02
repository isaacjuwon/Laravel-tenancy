<?php

namespace Isaacjuwon\LaravelTenancy\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Isaacjuwon\LaravelTenancy\Scopes\TenantScope;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            $model->{$model->getTenantForeignKey()} ??= tenant()?->getKey();
        });
    }

    /**
     * The relationship to the owning tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(config('tenancy.model'), $this->getTenantForeignKey());
    }

    /**
     * The foreign key on this model that stores the owning tenant's id.
     * Override on the model to customize, e.g. `protected $tenantForeignKey = 'company_id';`
     */
    public function getTenantForeignKey(): string
    {
        return $this->tenantForeignKey ?? 'tenant_id';
    }
}

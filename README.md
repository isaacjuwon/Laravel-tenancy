# Laravel Tenancy

A simple, lightweight tenancy package for Laravel.

Single-database multi-tenancy for Laravel 13 — no external dependencies, no
magic you can't read in five minutes. Just traits, two events, a global
scope, and a service provider that wires it all together.

If you've ever looked at a "tenancy" package and wondered why it needed forty
config options and a separate database connection just to scope a query by
`tenant_id`, this is the other end of that spectrum.

## Why this exists

Laravel's been leaning harder into attributes lately — `#[Singleton]`,
`#[Scoped]`, `#[Bind]` — letting the container read intent straight off the
class instead of you wiring it up by hand in a provider. This package
follows that spirit: tenancy should be something you *declare* on a model
(`use AsTenant`, `use BelongsToTenant`), not something you configure through
a maze of service bindings.

## Contents

- [Install](#install)
- [Setup](#setup)
- [How it works](#how-it-works)
- [Helpers](#helpers)
- [Config](#config)


## Install

```bash
composer require isaacjuwon/laravel-tenancy
```

Publish the config:

```bash
php artisan vendor:publish --tag=tenancy-config
```

## Setup

**1. Make your Tenant model a tenant**

```php
use Illuminate\Database\Eloquent\Model;
use Isaacjuwon\LaravelTenancy\Concerns\AsTenant;
use Isaacjuwon\LaravelTenancy\Contracts\Tenant as TenantContract;

class Tenant extends Model implements TenantContract
{
    use AsTenant;
}
```

Point `config/tenancy.php`'s `model` key at it (defaults to `App\Models\Tenant`).

**2. Scope tenant-owned models**

```php
use Illuminate\Database\Eloquent\Model;
use Isaacjuwon\LaravelTenancy\Concerns\BelongsToTenant;

class Invoice extends Model
{
    use BelongsToTenant;
}
```

That's it. Every query against `Invoice` is now scoped to the active tenant,
and new records get `tenant_id` filled in automatically.

- Using a different column? `protected $tenantForeignKey = 'company_id';`
- Need to peek across tenants? `Invoice::withoutTenancy()->get()`

**3. Add the foreign key to your migration**

```php
$table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
```

## How it works

| What | How |
|---|---|
| Resolving the current tenant | `tenant()` helper or `Tenant::current()` — reads a bound container instance, nothing request-global |
| Switching tenants | `$tenant->use()` binds it into the container, tags `Context` with its id, fires `UsingTenant` |
| Clearing the tenant | `$tenant->forget()` unbinds it, clears `Context`, fires `ForgettingTenant` |
| Running as a tenant temporarily | `$tenant->run(fn () => ...)` — restores whichever tenant was active before, great for admin tooling or artisan commands that loop over every tenant |
| Web requests | The provider resolves the tenant from the request host (`domain` or `subdomain` strategy) and calls `use()` for you |
| Queued jobs | The tenant id rides along via `Context` and gets re-hydrated before the job runs, then cleared after |
| Per-tenant config | `ConfigureTenant` — a `#[Singleton]` listener — swaps runtime config (mail sender, disk, cache prefix, whatever) when a tenant becomes active, and restores the original values when it doesn't |

## Helpers

The package ships one global helper, `tenant()`. It's a thin wrapper around
`Tenant::current()` — use whichever reads better in context.

```php
function tenant(): ?Isaacjuwon\LaravelTenancy\Contracts\Tenant
{
    return app()->has('tenant') ? app('tenant') : null;
}
```

It's nullable on purpose — there's no active tenant during `artisan` commands,
in queue workers between jobs, or on routes that aren't tenant-scoped, so
always guard against `null` unless you're certain a tenant is active.

**In a controller**

```php
public function index()
{
    // tenant() is already applied automatically to scoped models,
    // but you'll reach for it directly for anything not model-bound.
    return view('dashboard', [
        'tenant' => tenant(),
        'plan' => tenant()?->plan ?? 'free',
    ]);
}
```

**In Blade**

```blade
@if (tenant())
    <p>You're viewing {{ tenant()->name }}'s workspace.</p>
@endif
```

**In a policy or gate**

```php
public function update(User $user, Invoice $invoice): bool
{
    return $invoice->tenant_id === tenant()?->id;
}
```

**In an Artisan command that loops over every tenant**

`tenant()` is only set when something calls `use()`. To do work "as" each
tenant in turn, drive it explicitly:

```php
Tenant::query()->each(fn (Tenant $t) => $t->run(function () {
    // tenant() is set here for the duration of the closure,
    // then automatically restored to whatever it was before.
    Invoice::where('status', 'overdue')->each->sendReminder();
}));
```

**In tests**

```php
protected function setUp(): void
{
    parent::setUp();

    Tenant::factory()->create()->use();
}
```

Since `tenant()` reads from a bound container instance rather than a static
property, `->use()` in a test's `setUp()` is enough — no request faking, no
middleware, nothing to tear down beyond the normal test container reset.

**In Tinker**

```php
>>> Tenant::first()->use();
>>> tenant()->name;
=> "Acme Inc"
>>> tenant()->forget();
```

**Checking whether you're inside a tenant context at all**

```php
if (! tenant()) {
    abort(403, 'No active tenant.');
}
```



```php
return [
    'model' => \App\Models\Tenant::class,
    'identify_by' => 'domain', // 'domain' | 'subdomain' | false
    'domain_column' => 'domain',
    'context_key' => 'tenantId',
];
```

## License

MIT — do what you like with it.

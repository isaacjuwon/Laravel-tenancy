<?php

namespace Isaacjuwon\LaravelTenancy;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Isaacjuwon\LaravelTenancy\Events\ForgettingTenant;
use Isaacjuwon\LaravelTenancy\Events\UsingTenant;
use Isaacjuwon\LaravelTenancy\Listeners\ConfigureTenant;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tenancy.php', 'tenancy');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/tenancy.php' => config_path('tenancy.php'),
        ], 'tenancy-config');

        // ConfigureTenant is marked #[Singleton], so Laravel's container
        // resolves it once without any manual binding here.
        $this->app['events']->listen(UsingTenant::class, [ConfigureTenant::class, 'handleUsingTenant']);
        $this->app['events']->listen(ForgettingTenant::class, [ConfigureTenant::class, 'handleForgettingTenant']);

        $this->configureRequests();
        $this->configureQueues();
    }

    /**
     * Resolve the tenant for the incoming request, based on the configured
     * identification strategy.
     */
    protected function configureRequests(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $strategy = config('tenancy.identify_by');

        if (! $strategy) {
            return;
        }

        $model = config('tenancy.model');
        $column = config('tenancy.domain_column', 'domain');

        $host = $strategy === 'subdomain'
            ? explode('.', request()->getHost())[0]
            : request()->getHost();

        $model::where($column, $host)->first()?->use();
    }

    /**
     * Configure queued jobs to run within the tenant context that was
     * active when the job was dispatched.
     */
    protected function configureQueues(): void
    {
        Queue::before(function (JobProcessing $event) {
            $model = config('tenancy.model');
            $key = config('tenancy.context_key', 'tenantId');

            ($id = Context::get($key))
                ? $model::find($id)?->use()
                : $model::current()?->forget();
        });
    }
}

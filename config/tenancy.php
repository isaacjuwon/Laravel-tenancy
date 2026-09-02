<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The model that represents a tenant in your application. It must use
    | the Isaacjuwon\LaravelTenancy\Concerns\AsTenant trait so the package can
    | resolve, switch, and forget the currently active tenant.
    |
    */

    'model' => \App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Identification Strategy
    |--------------------------------------------------------------------------
    |
    | How incoming HTTP requests resolve the current tenant.
    |
    | 'domain'    matches the full request host against `domain_column`.
    | 'subdomain' matches only the first segment of the host.
    | false       disables automatic resolution on requests entirely.
    |
    */

    'identify_by' => 'domain',

    'domain_column' => 'domain',

    /*
    |--------------------------------------------------------------------------
    | Context Key
    |--------------------------------------------------------------------------
    |
    | The Context key used to carry the active tenant's id across queued
    | jobs, so it can be re-hydrated when the job is processed.
    |
    */

    'context_key' => 'tenantId',

];

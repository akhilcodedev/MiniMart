<?php

namespace Modules\WebConfigurationManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\WebConfigurationManagement\Services\OtpService;

class OtpConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService();
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}

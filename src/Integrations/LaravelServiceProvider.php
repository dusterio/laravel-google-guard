<?php

namespace Dusterio\LaravelGoogleGuard\Integrations;

use Illuminate\Support\ServiceProvider;

/**
 * Class CustomQueueServiceProvider
 * @package App\Providers
 */
class LaravelServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->addRoutes();
    }

    /**
     * @return void
     */
    protected function addRoutes()
    {
        $this->app['router']->post('/auth/google', 'Dusterio\LaravelGoogleGuard\Http\LoginController@redirectToProvider');
        $this->app['router']->post('/auth/google/callback', 'Dusterio\LaravelGoogleGuard\Http\LoginController@handleProviderCallback');
    }

    /**
     * @return void
     */
    public function boot()
    {
    }
}

<?php

namespace Dusterio\LaravelGoogleGuard\Integrations;

use Dusterio\LaravelGoogleGuard\GoogleGuard;
use Illuminate\Support\Facades\Auth;
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
        $this->app['router']->get('/auth/google', 'Dusterio\LaravelGoogleGuard\Http\LoginController@redirectToProvider');
        $this->app['router']->get('/auth/google/callback', 'Dusterio\LaravelGoogleGuard\Http\LoginController@handleProviderCallback');
    }

    /**
     * @return void
     */
    public function boot()
    {
        Auth::extend('google', function ($app) {
            $config = config('auth.guards.google');

            return new GoogleGuard($app['session.store'], $config['timeout'], $config['userClass'], $config['whitelist']);
        });
    }
}

<?php

namespace Valkyrie\Packageek;

use Illuminate\Support\ServiceProvider;

class PackageekServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The console commands.
     *
     * @var bool
     */
    protected $commands = [
        'Valkyrie\Packageek\NewPackage',
        'Valkyrie\Packageek\EditPackage',
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

   /**
    * Register the application services.
    *
    * @return void
    */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('packageek');
    }
}
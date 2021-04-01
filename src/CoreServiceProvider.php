<?php

namespace W7AddonCore;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use W7AddonCore\Commands\BuildCommand;

class CoreServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Application::class, function () {
            return new Application;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->publishPath('/Bootstrap/app.php') => base_path('/bootstrap/app.php'),
            $this->publishPath('/Command/BuildCommand.php') => app_path('/Console/Commands/BuildCommand.php'),
            $this->publishPath('/Config/app.php') => config_path('app.php'), 
            $this->publishPath('/Http/Kernel.php') => app_path('/Http/Kernel.php'),
            $this->publishPath('/Public/index.php') => public_path('index.php'),
            $this->publishPath('/artisan') => base_path('artisan'),
            $this->publishPath('/build.sh') => base_path('build.sh')
        ]);
    }

    protected function publishPath($path) {
        return __DIR__.'/Publishes'.$path;
    }
}

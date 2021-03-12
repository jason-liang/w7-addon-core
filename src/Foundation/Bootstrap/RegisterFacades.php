<?php

namespace W7AddonCore\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use W7AddonCore\Foundation\AliasLoader;
use W7AddonCore\Foundation\PackageManifest;
use Illuminate\Support\Facades\Facade;

class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance(array_merge(
            $app->make('config')->get('app.aliases', []),
            $app->make(PackageManifest::class)->aliases()
        ))->register();
    }
}

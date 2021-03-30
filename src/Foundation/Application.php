<?php

namespace W7AddonCore\Foundation;

use Illuminate\Foundation\Application as IlluminateApplication;

class Application extends IlluminateApplication {
    protected $we7Path;

    protected $moduleName;

    protected $modulePath;

    protected $distPath;

    public function __construct($basePath = null, $moduleName = null)
    {
        parent::__construct($basePath);

        $this->moduleName = $moduleName;

        if ($basePath) {
            $this->modulePath = dirname($basePath);
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.we7', $this->we7Path());
        $this->instance('path.we7Data', $this->we7DataPath());
        $this->instance('path.module', $this->modulePath());
        $this->instance('path.dist', $this->distPath());
        $this->instance('path.lang', $this->langPath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
    }

    public function getModuleName() {
        return $this->moduleName;
    }

    public function we7Path($path = '') {
        $we7Path = $this->we7Path ? $this->we7Path : dirname($this->basePath, 3);
        return $we7Path.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    public function we7DataPath($path = '') {
        $we7DataPath = $this->we7Path().DIRECTORY_SEPARATOR.'data';
        return $we7DataPath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    public function modulePath($path = '') {
        return $this->modulePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    public function distPath() {
        return $this->modulePath().DIRECTORY_SEPARATOR.'dist';
    }

    public function storagePath()
    {
        return $this->storagePath ?: $this->we7DataPath().DIRECTORY_SEPARATOR.$this->moduleName.DIRECTORY_SEPARATOR.'storage';
    }
}

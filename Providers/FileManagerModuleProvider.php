<?php

namespace RabbitCMS\FileManager\Providers;

use RabbitCMS\Modules\ModuleProvider;

class FileManagerModuleProvider extends ModuleProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot() { }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function name()
    {
        return 'filemanager';
    }
}

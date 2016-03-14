<?php namespace RabbitCMS\FileManager\Providers;

use Illuminate\Foundation\Application;

use RabbitCMS\Carrot\Providers\ModuleProvider;
use RabbitCMS\FileManager\FileSystem\Media;

class FileManagerServiceProvider extends ModuleProvider
{

    protected function name()
    {
        return 'filemanager';
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->make('config')->set('filesystems.disks.media', [
            'driver' => 'media',
        ]);

        \Storage::extend('media', function (Application $app, $config) {
            return new Media();
        });

        \BackendMenu::addItem('system', 'filemanager', trans('File manager'), '/blanks/blanks', 'fa-bars', ['system.filemanager']);

        \BackendAcl::add([
            'system.filemanager' => trans('File manager'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}

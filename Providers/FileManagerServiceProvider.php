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
       //    \BackendMenu::addMenu('system', trans('System'), ['system.* ']);
        \BackendMenu::addItem('system', 'filemanager', trans('File manager'), route('backend.filemanager.index', [], false), 'fa-bars', ['system.filemanager']);

        \BackendAcl::add([
            'system.filemanager' => trans('File manager'),
        ]);
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

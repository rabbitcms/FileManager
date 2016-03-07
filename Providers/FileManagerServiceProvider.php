<?php namespace RabbitCMS\Filemanager\Providers;

use Illuminate\Support\ServiceProvider;
use RabbitCMS\Backend\Providers\ModuleProvider;

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
        dd();

        \BackendMenu::addMenu('system', '', trans('System'), 'fa-file-text', ['system.*']);
        \BackendMenu::addItem('system', 'filemanager', trans('File manager'), '/blanks/blanks', 'fa-bars', ['system.filemanager.*']);

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

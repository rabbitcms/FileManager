<?php

namespace RabbitCMS\FileManager\Http\Controllers\Backend;

use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\View;

class ElfinderController extends Controller
{
    public function init(ViewFactory $factory)
    {
        $factory->composer([$this->viewName('elfinder'), $this->viewName('standalonepopup')], function (View $view) {
            $locale = str_replace('-', '_', $this->app->getLocale());
            if (!file_exists(public_path('modules/filemanager/plugins/elfinder/js/i18n/elfinder.' . $locale . '.js'))) {
                $locale = false;
            }

            $view->with('locale', $locale);
        });
    }

    public function showIndex()
    {
        return $this->view('elfinder');
    }

    public function showTinyMCE()
    {
        return $this->view('tinymce');
    }

    public function showTinyMCE4()
    {
        return $this->view('tinymce4');
    }

    public function showCKeditor4()
    {
        return $this->view('ckeditor4');
    }

    public function showPopup($input_id = 'xsd')
    {
        return $this->view('standalonepopup')
            ->with(compact('input_id'));
    }

    public function showFilePicker($input_id)
    {
        $type = Request::input('type');

        return $this->view('filepicker')
            ->with($this->getViewVars())
            ->with(compact('input_id', 'type'));
    }

    public function showConnector()
    {
        $roots = $this->config('roots', []);
        if (empty($roots)) {
            $dirs = (array)$this->config('dir', []);
            foreach ($dirs as $dir) {
                if (file_exists(public_path($dir)) === false) {
                    mkdir(public_path($dir));
                }

                $roots[] = [
                    'driver'        => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                    'path'          => public_path($dir), // path to files (REQUIRED)
                    'URL'           => url($dir), // URL to files (REQUIRED)
                    'accessControl' => $this->config('access') // filter callback (OPTIONAL)
                ];
            }

            $disks = (array)$this->config('disks', []);
            foreach ($disks as $key => $root) {
                if (is_string($root)) {
                    $key = $root;
                    $root = [];
                }
                $disk = app('filesystem')->disk($key);
                if ($disk instanceof FilesystemAdapter) {
                    $defaults = [
                        'driver'     => 'Flysystem',
                        'filesystem' => $disk->getDriver(),
                        'alias'      => $key,
                    ];
                    $roots[] = array_merge($defaults, $root);
                }
            }
        }

        $opts = $this->config('options', array());
        $opts = array_merge(['roots' => $roots], $opts);

        // run elFinder
        $connector = new Connector(new \elFinder($opts));
        $connector->run();

        return $connector->getResponse();
    }
}

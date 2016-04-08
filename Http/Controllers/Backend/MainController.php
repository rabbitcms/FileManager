<?php

namespace RabbitCMS\FileManager\Http\Controllers\Backend;

use RabbitCMS\FileManager\Http\Controllers\Controller;
use RabbitCMS\Backend\Support\Metronic;

class MainController extends Controller
{

    public function index()
    {
        Metronic::addPath(trans('Home'), '/');
        Metronic::addPath(trans('FileManager'), null);
        Metronic::menu('system','filemanager');
        return $this->view('backend.main')
            ->with($this->getViewVars());
    }

    protected function getViewVars()
    {
        $dir = '/bower/elfinder';
        $locale = str_replace('-', '_', app()->getLocale());
        if (!file_exists(public_path("{$dir}/js/i18n/elfinder.{$locale}.js"))) {
            $locale = false;
        }
        $csrf = true;

        return compact('dir', 'locale', 'csrf');
    }

}
<?php

use Illuminate\Routing\Router;

return [
    'routes'    => function (Router $router) {

        $router->group(['prefix' => 'elfinder'], function (Router $router) {
            $router->get('/', ['as' => 'elfinder.standalone', 'uses' => 'ElfinderController@showIndex']);

            $router->get('popup/{input_id?}', ['as' => 'elfinder.popup', 'uses' => 'ElfinderController@showPopup']);

            $router->any('connector', ['as' => 'elfinder.connector', 'uses' => 'ElfinderController@showConnector']);

            /*

            $router->get('filepicker/{input_id}', ['as' => 'elfinder.filepicker', 'uses' => 'ElfinderController@showFilePicker']);
            $router->get('tinymce', ['as' => 'elfinder.tinymce', 'uses' => 'ElfinderController@showTinyMCE']);
            $router->get('tinymce4', ['as' => 'elfinder.tinymce4', 'uses' => 'ElfinderController@showTinyMCE4']);
            $router->get('ckeditor', ['as' => 'elfinder.ckeditor', 'uses' => 'ElfinderController@showCKeditor4']);*/
        });
    },
    'requirejs' => [
        'packages' => [
            'rabbitcms.filemanager' => [
                'location' => 'js',
                'main'     => 'filemanager'
            ]
        ]
    ]
];

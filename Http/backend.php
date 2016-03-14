<?php

\Route::group(['prefix'=>'elfinder','namespace'=>'\RabbitCMS\FileManager\Http\Controllers\Backend'], function(\Illuminate\Routing\Router $router)
{
    $router->get('/', 'ElfinderController@showIndex');
    $router->any('connector', ['as' => 'elfinder.connector', 'uses' => 'ElfinderController@showConnector']);
    $router->get('popup/{input_id}', ['as' => 'elfinder.popup', 'uses' => 'ElfinderController@showPopup']);
    $router->get('filepicker/{input_id}', ['as' => 'elfinder.filepicker', 'uses' => 'ElfinderController@showFilePicker']);
    $router->get('tinymce', ['as' => 'elfinder.tinymce', 'uses' => 'ElfinderController@showTinyMCE']);
    $router->get('tinymce4', ['as' => 'elfinder.tinymce4', 'uses' => 'ElfinderController@showTinyMCE4']);
    $router->get('ckeditor', ['as' => 'elfinder.ckeditor', 'uses' => 'ElfinderController@showCKeditor4']);
});
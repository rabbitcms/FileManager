<?php namespace RabbitCMS\FileManager\Http\Controllers;


use RabbitCMS\Carrot\Http\ModuleController;

class FileManagerController extends ModuleController {
	
	public function index()
	{
		return view('filemanager::index');
	}
	
}
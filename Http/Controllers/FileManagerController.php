<?php namespace Modules\Filemanager\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class FileManagerController extends Controller {
	
	public function index()
	{
		return view('filemanager::index');
	}
	
}
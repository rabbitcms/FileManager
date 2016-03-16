@extends('backend::layouts.master')
@section('content')
    <iframe src="<?= route('backend.filemanager.elfinder.standalone', [], false);?>" style="border: 0;width: 100%;height: 500px;height: 80vh;"></iframe>
@stop

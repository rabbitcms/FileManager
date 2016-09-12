<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label">Зображення</label>
            <div class="input-group picker-container">
                <input type="hidden" name="{{$name}}" class="file" value="{{$picture}}">
                <input type="text" class="form-control file-name" readonly rel="popup" value="{{$picture !== null ? basename($picture) : 'Файл не вибрано'}}" placeholder="Файл не вибрано">
                <span class="input-group-btn">
                    <a class="btn default" rel="popup" type="button">
                        <i class="icon-refresh"></i></a></span>
                <span class="input-group-btn">
                    <a class="btn blue" rel="preview" type="button" href="{{$picture}}" target="_blank">
                        <i class="fa fa-eye"></i></a></span>
                <span class="input-group-btn">
                    <a class="btn red" rel="delete" href="javascript:void(0);">
                        <i class="fa fa-trash"></i></a></span>
            </div>
        </div>
    </div>
</div>
define(["require", "exports", "jquery", "rabbitcms/backend"], function (require, exports, $, backend_1) {
    "use strict";
    var FileManager = (function () {
        function FileManager() {
        }
        FileManager.init = function (input) {
            var _this = this;
            var filePicker = input.parent('.picker-container');
            var previewBtn = $('[rel="preview"]', filePicker);
            var clearBtn = $('[rel="delete"]', filePicker);
            var fileName = $('.file-name', filePicker);
            filePicker.on('click', '[rel="popup"]', function (e) {
                e.preventDefault();
                _this.load().then(function (file) {
                    var path = '/' + file.path;
                    fileName.val(file.name);
                    previewBtn.attr('href', path);
                    input.val(path).change();
                });
            });
            input.on('change', function (e) {
                var file = $(e.currentTarget).val();
                if (!file) {
                    previewBtn.parent('.input-group-btn').hide();
                    clearBtn.parent('.input-group-btn').hide();
                }
                else {
                    previewBtn.parent('.input-group-btn').show();
                    clearBtn.parent('.input-group-btn').show();
                }
            }).trigger('change');
            clearBtn.on('click', function () {
                input.val('').change();
                fileName.val('');
            });
            previewBtn.on('click', function (e) {
                e.preventDefault();
                $.colorbox({
                    href: $(e.currentTarget).attr('href'),
                    photo: true,
                    width: '70%',
                    height: '580'
                });
            });
        };
        FileManager.load = function () {
            return new Promise(function (resolve, reject) {
                backend_1.RabbitCMS.colorBox({
                    href: '/filemanager/elfinder/popup',
                    fastIframe: true,
                    iframe: true,
                    width: '85%',
                    height: '85%'
                });
                function processSelectedFile(path, file) {
                    resolve({
                        mime: file.mime,
                        name: file.name,
                        path: path,
                        size: parseInt(file.size),
                        url: file.url
                    });
                }
                window['processSelectedFile'] = processSelectedFile;
            });
        };
        return FileManager;
    }());
    exports.FileManager = FileManager;
});
//# sourceMappingURL=filemanager.js.map
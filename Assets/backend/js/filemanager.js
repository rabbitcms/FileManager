define(["require", "exports", "rabbitcms/backend"], function (require, exports, backend_1) {
    "use strict";
    var FileManager = (function () {
        function FileManager() {
        }
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
/// <reference path="../../../../backend/Assets/backend/dt/index.d.ts" />

import * as $ from "jquery";
import {RabbitCMS} from "rabbitcms/backend";

export interface File {
    mime: string;
    name: string;
    path: string;
    size: number;
    url: string;
}

export class FileManager {
    static init(input:JQuery) {

        let filePicker = input.parent('.picker-container');
        let previewBtn = $('[rel="preview"]', filePicker);
        let clearBtn = $('[rel="delete"]', filePicker);
        let fileName = $('.file-name', filePicker);

        filePicker.on('click', '[rel="popup"]', (e) => {
            e.preventDefault();

            this.load().then((file) => {
                let path = '/' + file.path;
                fileName.val(file.name);
                previewBtn.attr('href', path);
                input.val(path).change();
            });
        });

        input.on('change', (e) => {
            let file = $(e.currentTarget).val();

            if (!file) {
                previewBtn.parent('.input-group-btn').hide();
                clearBtn.parent('.input-group-btn').hide();
            } else {
                previewBtn.parent('.input-group-btn').show();
                clearBtn.parent('.input-group-btn').show();
            }
        }).trigger('change');

        clearBtn.on('click', () => {
            input.val('').change();
            fileName.val('');
        });

        previewBtn.on('click', (e) => {
            e.preventDefault();

            $.colorbox({
                href: $(e.currentTarget).attr('href'),
                photo: true,
                width: '70%',
                height: '580'
            });
        });
    }

    static load():Promise<File> {
        return new Promise((resolve, reject) => {
            RabbitCMS.colorBox({
                href: '/filemanager/elfinder/popup',
                fastIframe: true,
                iframe: true,
                width: '85%',
                height: '85%'
            });

            function processSelectedFile(path: string, file: any) {
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
    }
}

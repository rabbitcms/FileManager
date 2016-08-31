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

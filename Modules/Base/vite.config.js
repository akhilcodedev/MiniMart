import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

let publicMainAssets = './../../public';
let moduleOutputDir = 'build-base';
let moduleAssetDir = __dirname + '/resources/assets';

export default defineConfig({
    build: {
        outDir: publicMainAssets + '/' + moduleOutputDir,
        emptyOutDir: true,
        manifest: true,
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler' /* or "modern" */
            }
        }
    },
    plugins: [
        viteStaticCopy({
            targets: [
                {
                    src: moduleAssetDir + '/ktmt/*',
                    dest: 'ktmt'
                }
            ]
        }),
        laravel({
            publicDirectory: publicMainAssets,
            buildDirectory: moduleOutputDir,
            input: [
                moduleAssetDir + '/sass/app.scss',
                moduleAssetDir + '/sass/responsive.scss',
                moduleAssetDir + '/js/app.js',
                moduleAssetDir + '/js/themeColor.js',
            ],
            refresh: true,
        }),
    ],
});

//export const paths = [
//    'Modules/Base/resources/assets/sass/app.scss',
//    'Modules/Base/resources/assets/js/app.js',
//];

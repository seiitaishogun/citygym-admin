const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('public')
    .setResourceRoot('../') // Turns assets paths in css relative to css file
    .sass('resources/sass/frontend/app.scss', 'css/frontend.css')
    .sass('resources/sass/backend/app.scss', 'css/backend.css')
    .js('resources/js/frontend/app.js', 'js/frontend.js')
    .js('resources/js/backend/app.js', 'js/backend.js')
    .extract([
        'alpinejs',
        'jquery',
        'bootstrap',
        'popper.js',
        'axios',
        'sweetalert2',
        'lodash'
    ])
    .sourceMaps();

if (mix.inProduction()) {
    mix.version();
} else {
    // Uses inline source-maps on development
    mix.webpackConfig({
        devtool: 'inline-source-map'
    });

    //setup tinymce
    mix.copyDirectory('node_modules/tinymce/icons', 'public/plugins/tinymce/icons');
    mix.copyDirectory('node_modules/tinymce/plugins', 'public/plugins/tinymce/plugins');
    mix.copyDirectory('node_modules/tinymce/skins', 'public/plugins/tinymce/skins');
    mix.copyDirectory('node_modules/tinymce/themes', 'public/plugins/tinymce/themes');
    mix.copy('node_modules/tinymce/jquery.tinymce.js', 'public/plugins/tinymce/jquery.tinymce.js');
    mix.copy('node_modules/tinymce/jquery.tinymce.min.js', 'public/plugins/tinymce/jquery.tinymce.min.js');
    mix.copy('node_modules/tinymce/tinymce.js', 'public/plugins/tinymce/tinymce.js');
    mix.copy('node_modules/tinymce/tinymce.min.js', 'public/plugins/tinymce/tinymce.min.js');

    //set custom js
    mix.copy('resources/js/backend/modules/notification.js', 'public/js/backend/modules/notification.js');
}

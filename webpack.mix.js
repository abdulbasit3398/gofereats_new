let mix = require('laravel-mix');
mix.setResourceRoot('../');
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

 mix.js('resources/assets/js/app.js', 'public/js')
 .sass('resources/assets/sass/common.scss', 'public/css')
 .sass('resources/assets/sass/home.scss', 'public/css')
 .sass('resources/assets/sass/category.scss', 'public/css')
 .sass('resources/assets/sass/details.scss', 'public/css')
 .sass('resources/assets/sass/login.scss', 'public/css')
 .sass('resources/assets/sass/register.scss', 'public/css')
 .sass('resources/assets/sass/header.scss', 'public/css')
 .sass('resources/assets/sass/footer.scss', 'public/css')
 .sass('resources/assets/sass/otherpages.scss', 'public/css')
 .sass('resources/assets/sass/admin/common.scss', 'public/admin_assets/css')
 .copy('resources/assets/fonts', 'public/admin_assets/fonts');

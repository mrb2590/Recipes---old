const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .js('resources/js/app.js', 'public/js')
  .vue()
  .postCss('resources/css/app.css', 'public/css', [require('postcss-import'), require('tailwindcss')])
  .webpackConfig(require('./webpack.config'))
  .extract([
    '@inertiajs/inertia',
    '@inertiajs/inertia-vue3',
    '@inertiajs/progress',
    '@tailwindcss/forms',
    '@tailwindcss/typography',
    '@vue/compiler-sfc',
    'axios',
    'laravel-mix',
    'lodash',
    'postcss',
    'postcss-import',
    'tailwindcss',
    'vue',
    'vue-loader',
  ]);

if (mix.inProduction()) {
  mix.version();
} else {
  mix.sourceMaps();
}

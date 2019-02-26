let mix = require('laravel-mix')

mix.setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .sass('resources/sass/field.scss', 'css')
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './../../laravel/nova/resources/js/'),
            },
        },
    })
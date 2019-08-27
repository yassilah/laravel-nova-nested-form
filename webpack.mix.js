let mix = require('laravel-mix')

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .webpackConfig({
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './vendor/laravel/nova/resources/js/')
      }
    }
  })

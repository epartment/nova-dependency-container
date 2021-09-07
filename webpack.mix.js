let mix = require('laravel-mix');

mix
  .js('resources/js/field.js', 'dist/js')
  .vue()
  .webpackConfig({
    resolve: {
      symlinks: false,
    },
  });

let mix = require('laravel-mix');

require('./nova.mix.js');

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({ version: 3 })
    .nova('workup/nova-dependency-container');

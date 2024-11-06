const mix = require('laravel-mix')
const webpack = require('webpack')

mix.setPublicPath('src/public')
mix.setResourceRoot('/sampurna/');
mix.sass('src/resources/scss/sampurna.scss', 'css')
mix.js('src/resources/js/sampurna.js', 'js').vue()

if (mix.inProduction()) {
    mix.webpackConfig({
        output: {
            filename: '[name].js',
            chunkFilename: 'js/[name].app.js',
            publicPath: '/sampurna/'
        }
    })
    mix.version();
} else {
    mix.webpackConfig({
        output: {
            filename: '[name].js',
            chunkFilename: 'js/[name].app.js',
            publicPath: '/sampurna/'
        },
        devtool: 'inline-source-map',
        plugins:[
            new webpack.DefinePlugin({
                __VUE_OPTIONS_API__: JSON.stringify(true),
                __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
                __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
            })
        ]
    })
}

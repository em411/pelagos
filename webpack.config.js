// webpack.config.js
var Encore = require('@symfony/webpack-encore');

const CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    // the project directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath(Encore.isProduction() ? '/pelagos-symfony/build' : '/build')

    // will create web/build/app.js and web/build/app.css
    .createSharedEntry('common', './assets/js/common.js')
    .addEntry('layout', './assets/js/layout.js')

    // allow sass/scss files to be processed
    //.enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    //.enableBuildNotifications()

    // Add or change path of build asset location
    .setManifestKeyPrefix('build/')

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // No runtime.js needed.
    .disableSingleRuntimeChunk()

    .addPlugin(new CopyWebpackPlugin([
        // copies to {output}/static
        {from: './assets/images', to: 'images'},
        {from: './src/Pelagos/Bundle/AppBundle/Resources/public/js', to: 'js'},
        {from: './src/Pelagos/Bundle/AppBundle/Resources/public/css', to: 'css'},
        {from: './src/Pelagos/Bundle/AppBundle/Resources/public/images', to: 'images'},
        {from: './src/Pelagos/Bundle/AppBundle/Resources/public/js/entity', to: 'js/entity'},
    ]))
;

// export the final configuration
module.exports = Encore.getWebpackConfig();

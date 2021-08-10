const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');

module.exports = {
    mode: 'production',
    entry: {
        'js/datatables': './assets/js/index.js',
        'css/datatables': './assets/scss/index.scss'
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, './public/'),
        clean: true
    },
    module: {
        rules: [
            {
                test: /\.s[ac]ss$/i,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath: '',
                        },
                    },
                    {
                        loader: 'css-loader',
                    },
                    {
                        loader: 'postcss-loader',
                    },
                    {
                        loader: 'sass-loader',
                    },
                ]
            }, {
                test: /\.vue$/,
                loader: 'vue-loader'
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            filename: ({ chunk }) => `${chunk.name.replace('/js/', '/css/')}.css`,
        }),
        new FixStyleOnlyEntriesPlugin()
    ]
};

const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    ...defaultConfig,
    entry: {
        index: './src/index.js', // Point d'entrée principal pour JS
        categoryAccordion: './src/category-accordion.js', // Point d'entrée pour le bloc "category-accordion"
        categoryAccordionEditor: './src/category-accordion-editor.js', // Point d'entrée pour l'éditeur du bloc "category-accordion"
        'style': './src/style.css', // Fichier CSS commun
        'style-common': './src/style-common.css', // Fichier CSS commun supplémentaire
        'category-accordion': './src/category-accordion.css', // Style spécifique au bloc "category-accordion"
        'product-grid': './src/product-grid.css', // Style spécifique au bloc "product-grid"
        'category-accordion-editor': './src/category-accordion-editor.css', // Style éditeur pour "category-accordion"
        'product-grid-editor': './src/product-grid-editor.css', // Style éditeur pour "product-grid"
    },
    output: {
        path: path.resolve(__dirname, 'build'), // Dossier de sortie
        filename: '[name].js', // Nom des fichiers JS générés
    },
    module: {
        ...defaultConfig.module,
        rules: [
            ...defaultConfig.module.rules.filter(rule =>
                !rule.test.toString().includes('css')
            ),
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader, // Extrait les styles dans des fichiers séparés
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                            modules: false
                        }
                    },
                    'postcss-loader'
                ]
            }
        ]
    },
    plugins: [
        ...defaultConfig.plugins,
        new MiniCssExtractPlugin({
            filename: '[name].css', // Génère un fichier CSS pour chaque entrée
        })
    ]
};

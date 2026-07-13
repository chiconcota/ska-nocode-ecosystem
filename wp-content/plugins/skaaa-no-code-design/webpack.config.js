const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'skaaa-container/index': path.resolve(process.cwd(), 'src/skaaa-container', 'index.js'),
        'skaaa-text/index': path.resolve(process.cwd(), 'src/skaaa-text', 'index.js'),
        'skaaa-image/index': path.resolve(process.cwd(), 'src/skaaa-image', 'index.js'),
        'skaaa-icon/index': path.resolve(process.cwd(), 'src/skaaa-icon', 'index.js'),
        'skaaa-button/index': path.resolve(process.cwd(), 'src/skaaa-button', 'index.js'),
        'skaaa-video/index': path.resolve(process.cwd(), 'src/skaaa-video', 'index.js'),
        'skaaa-list/index': path.resolve(process.cwd(), 'src/skaaa-list', 'index.js'),
        'skaaa-list-item/index': path.resolve(process.cwd(), 'src/skaaa-list-item', 'index.js'),
        'skaaa-bridge-import/index': path.resolve(process.cwd(), 'src/skaaa-bridge-import', 'index.js'),

        'skaaa-input/index': path.resolve(process.cwd(), 'src/skaaa-input', 'index.js'),
        'skaaa-select/index': path.resolve(process.cwd(), 'src/skaaa-select', 'index.js'),
        'skaaa-form-rich-text/index': path.resolve(process.cwd(), 'src/skaaa-form-rich-text', 'index.js'),
        'skaaa-organism-ref/index': path.resolve(process.cwd(), 'src/skaaa-organism-ref', 'index.js'),
        'skaaa-loop/index': path.resolve(process.cwd(), 'src/skaaa-loop', 'index.js'),
        'skaaa-code/index': path.resolve(process.cwd(), 'src/skaaa-code', 'index.js'),
        'extensions': path.resolve(process.cwd(), 'src/extensions/html-attributes.js'),
    },
    output: {
        path: path.resolve(process.cwd(), 'build'),
        filename: '[name].js',
    },
};

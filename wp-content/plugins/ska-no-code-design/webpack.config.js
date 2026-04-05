const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'ska-container/index': path.resolve(process.cwd(), 'src/ska-container', 'index.js'),
        'ska-text/index': path.resolve(process.cwd(), 'src/ska-text', 'index.js'),
        'ska-image/index': path.resolve(process.cwd(), 'src/ska-image', 'index.js'),
        'ska-icon/index': path.resolve(process.cwd(), 'src/ska-icon', 'index.js'),
        'ska-button/index': path.resolve(process.cwd(), 'src/ska-button', 'index.js'),
        'ska-video/index': path.resolve(process.cwd(), 'src/ska-video', 'index.js'),
        'ska-list/index': path.resolve(process.cwd(), 'src/ska-list', 'index.js'),
        'ska-list-item/index': path.resolve(process.cwd(), 'src/ska-list-item', 'index.js'),
        'ska-bridge-import/index': path.resolve(process.cwd(), 'src/ska-bridge-import', 'index.js'),
        'ska-form/index': path.resolve(process.cwd(), 'src/ska-form', 'index.js'),
        'ska-input/index': path.resolve(process.cwd(), 'src/ska-input', 'index.js'),
        'ska-select/index': path.resolve(process.cwd(), 'src/ska-select', 'index.js'),
    },
    output: {
        path: path.resolve(process.cwd(), 'build'),
        filename: '[name].js',
    },
};

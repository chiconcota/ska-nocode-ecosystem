const fs = require('fs');
const archiver = require('./ska-data-pro/node_modules/archiver');
const path = require('path');

const plugins = ['ska-no-code-design', 'ska-data-pro', 'ska-logic-engine'];

plugins.forEach(pluginFolder => {
    const outputFilePath = path.join(__dirname, `${pluginFolder}.zip`);
    const output = fs.createWriteStream(outputFilePath);
    const archive = archiver('zip', { zlib: { level: 9 } });

    output.on('close', function() {
        console.log(`[Thành công] Đã đóng gói: ${pluginFolder}.zip (${(archive.pointer() / 1024 / 1024).toFixed(2)} MB)`);
    });

    archive.on('error', function(err) { throw err; });
    archive.pipe(output);

    archive.glob('**/*', {
        cwd: path.join(__dirname, pluginFolder),
        ignore: [
            '**/node_modules/**', 
            '**/src/**', 
            'package.json', 
            'package-lock.json', 
            'webpack.config.js',
            'vite.config.js', 
            'build-zip.js',
            '.gitignore',
            '**/.git/**'
        ]
    }, { prefix: pluginFolder });

    archive.finalize();
});

const fs = require('fs');
const archiver = require('archiver');
const path = require('path');

const outputDir = path.join(__dirname, '..'); // Xuất ra wp-content/plugins/
const pluginFolder = 'skaaa-data-pro';
const outputFilePath = path.join(outputDir, `${pluginFolder}.zip`);

const output = fs.createWriteStream(outputFilePath);
const archive = archiver('zip', {
    zlib: { level: 9 } // Đặt mức nén tối đa
});

output.on('close', function() {
    console.log(`[Thành công] Plugin đã được đóng gói: ${outputFilePath}`);
    console.log(`[Dung lượng]: ${(archive.pointer() / 1024 / 1024).toFixed(2)} MB`);
});

archive.on('error', function(err) {
    throw err;
});

archive.pipe(output);

// Bỏ qua các file rác
archive.glob('**/*', {
    cwd: __dirname,
    ignore: [
        'node_modules/**', 
        'assets/js/src/**', 
        'package.json', 
        'package-lock.json', 
        'vite.config.js', 
        'build-zip.js',
        '.gitignore'
    ]
}, { prefix: pluginFolder }); // Khi bung nén sẽ nằm trong folder skaaa-data-pro/

archive.finalize();

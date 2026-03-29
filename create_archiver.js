const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const sourceDir = path.join(__dirname, 'wp-content', 'plugins', 'ska-builder-core');
const destZip = path.join(__dirname, 'ska-builder-core.zip');

const output = fs.createWriteStream(destZip);
const archive = archiver('zip', {
  zlib: { level: 9 }
});

output.on('close', function() {
  console.log('Total bytes: ' + archive.pointer());
  console.log('Zip file created successfully!');
});

archive.on('error', function(err) {
  throw err;
});

archive.pipe(output);

archive.file(path.join(sourceDir, 'ska-builder-core.php'), { name: 'ska-builder-core/ska-builder-core.php' });
if (fs.existsSync(path.join(sourceDir, 'package.json'))) archive.file(path.join(sourceDir, 'package.json'), { name: 'ska-builder-core/package.json' });

const dirsToZip = ['assets', 'blocks', 'build', 'inc', 'src', 'scripts']; // Excluding node_modules and .git by simply totally ignoring them

dirsToZip.forEach(dir => {
    const fullPath = path.join(sourceDir, dir);
    if (fs.existsSync(fullPath)) {
        // This maintains the directory tree exactly
        archive.directory(fullPath, 'ska-builder-core/' + dir);
    }
});

archive.finalize();

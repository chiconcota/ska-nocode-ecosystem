const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const sourceDir = path.join(__dirname, 'wp-content', 'plugins', 'ska-builder-core');
const tempBase = path.join(__dirname, 'ska-export-temp');
const targetDir = path.join(tempBase, 'ska-builder-core');
const destZip = path.join(__dirname, 'ska-builder-core.zip');

if (fs.existsSync(tempBase)) {
  fs.rmSync(tempBase, { recursive: true, force: true });
}
if (fs.existsSync(destZip)) {
  fs.unlinkSync(destZip);
}

fs.mkdirSync(targetDir, { recursive: true });

const items = ['assets', 'blocks', 'build', 'inc', 'src', 'scripts', 'ska-builder-core.php', 'package.json'];

items.forEach(item => {
  const src = path.join(sourceDir, item);
  const dest = path.join(targetDir, item);
  if (fs.existsSync(src)) {
    fs.cpSync(src, dest, { recursive: true });
  } else {
    console.log('Warning: Missing ' + src);
  }
});

try {
  execSync(`tar -a -c -f "${destZip}" ska-builder-core`, { cwd: tempBase });
  console.log('Zip created successfully. Size:', fs.statSync(destZip).size);
} catch (e) {
  console.error('Error creating zip:', e.toString());
}

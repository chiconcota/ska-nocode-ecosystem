const fs = require('fs');
const path = require('path');

/**
 * Sync PHP files from src to build directory.
 * This is a lightweight script to ensure WordPress loads the latest PHP logic
 * while maintaining the directory structure.
 */

const srcDir = path.resolve(__dirname, '../src');
const buildDir = path.resolve(__dirname, '../build');

function copyRecursiveSync(src, dest) {
    const exists = fs.existsSync(src);
    const stats = exists && fs.statSync(src);
    const isDirectory = exists && stats.isDirectory();

    if (isDirectory) {
        if (!fs.existsSync(dest)) {
            fs.mkdirSync(dest, { recursive: true });
        }
        fs.readdirSync(src).forEach((childItemName) => {
            copyRecursiveSync(
                path.join(src, childItemName),
                path.join(dest, childItemName)
            );
        });
    } else {
        // Skip JS files so compiled Webpack output in build is not overwritten by raw src JS.
        const ext = path.extname(src).toLowerCase();
        if (ext === '.js') {
            return;
        }

        // Copy remaining files (PHP, JSON, CSS, etc.)
        fs.copyFileSync(src, dest);
        console.log(`[Sync] Copied: ${path.relative(srcDir, src)}`);
    }
}

console.log('--- Starting Build Sync (PHP) ---');
if (!fs.existsSync(srcDir)) {
    console.error('Error: src directory does not exist.');
    process.exit(1);
}

try {
    copyRecursiveSync(srcDir, buildDir);
    console.log('--- Build Sync Completed Successfully ---');
} catch (err) {
    console.error('Error during sync:', err);
    process.exit(1);
}

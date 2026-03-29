<?php
$source_dir = __DIR__ . '/wp-content/plugins/ska-builder-core';
$dest_zip = __DIR__ . '/ska-builder-core.zip';

$zip = new ZipArchive();
if ($zip->open($dest_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Failed to create zip");
}

// Add the root directory first to ensure standard compatibility
$zip->addEmptyDir('ska-builder-core');

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $path = $item->getPathname();
    // Normalize path separators to forward slash
    $path = str_replace('\\', '/', $path);
    $normalized_source_dir = str_replace('\\', '/', $source_dir);
    
    $relative_path = 'ska-builder-core/' . ltrim(str_replace($normalized_source_dir, '', $path), '/');
    
    // Exact match exclusion for node_modules and .git
    if (preg_match('#/(node_modules|\.git)(/|$)#', '/' . $relative_path)) {
        continue;
    }

    if ($item->isDir()) {
        $zip->addEmptyDir($relative_path);
    } else {
        $zip->addFile($path, $relative_path);
    }
}

$zip->close();
echo "Zip created successfully! Size: " . filesize($dest_zip) . " bytes\n";

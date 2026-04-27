<?php
$plugins = ['ska-nocode-design', 'ska-data-pro', 'ska-logic-engine'];
$exclude_dirs = ['node_modules', '.git'];

foreach ($plugins as $plugin) {
    $source = "wp-content/plugins/$plugin";
    $destination = "$plugin.zip";
    
    if (!is_dir($source)) {
        echo "Directory $source not found.\n";
        continue;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                function ($current, $key, $iterator) use ($exclude_dirs) {
                    // Chặn thư mục không mong muốn
                    if ($current->isDir() && in_array($current->getFilename(), $exclude_dirs)) {
                        return false;
                    }
                    return true;
                }
            )
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                // Thay thế backslash thành slash trên Windows để tương thích tốt hơn khi nén
                $filePath = str_replace('\\', '/', $filePath);
                $source_path = str_replace('\\', '/', realpath($source));
                
                $relativePath = substr($filePath, strlen($source_path) + 1);
                $zip->addFile($filePath, "$plugin/$relativePath");
            }
        }
        $zip->close();
        echo "Thành công: Đã đóng gói $destination\n";
    } else {
        echo "Lỗi: Không thể tạo $destination\n";
    }
}

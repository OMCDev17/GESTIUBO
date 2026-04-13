<?php
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
$changed = 0;
foreach ($rii as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') continue;
    $data = file_get_contents($path);
    if ($data !== false && strncmp($data, "\xEF\xBB\xBF", 3) === 0) {
        file_put_contents($path, substr($data, 3));
        $changed++;
    }
}
echo "changed=$changed\n";

<?php
// Fix script: patches routes/channels.php to not crash on missing configs table
// Upload this file to public_html/ on the server, run it, then delete it.

$file = __DIR__ . '/routes/channels.php';
$content = file_get_contents($file);

$old = '(new SetConfig)->set();';
$new = 'try { if (\Illuminate\Support\Facades\Schema::hasTable(\'configs\')) { (new SetConfig)->set(); } } catch (\Throwable $e) { /* table not migrated yet */ }';

if (strpos($content, 'hasTable') !== false) {
    echo "Already patched!\n";
    exit(0);
}

if (strpos($content, $old) === false) {
    echo "ERROR: Could not find the target line in channels.php\n";
    exit(1);
}

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);

echo "SUCCESS: channels.php patched!\n";

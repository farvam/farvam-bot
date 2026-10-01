<?php
// One-time import of JSON data from an older file-based install into the SQLite database.
// Usage (inside the image): php /opt/panel-market/import-json.php /path/to/old/data
// Only datasets that are not in the database yet are imported; nothing is overwritten.
require '/var/www/html/farvam/lib.php';
$dir = rtrim($argv[1] ?? '', '/');
if (!db() || !is_dir($dir)) { fwrite(STDERR, "database or folder missing\n"); exit(1); }
foreach (glob("$dir/*.json") as $f) {
    $name = basename($f, '.json');
    if (str_starts_with($name, 'backup-') || !preg_match('/^[a-z0-9-]+$/', $name)) continue;
    $q = db()->prepare('SELECT 1 FROM store WHERE name = ?'); $q->execute([$name]);
    if ($q->fetchColumn()) { echo "skip $name (already in database)\n"; continue; }
    $d = json_decode((string)file_get_contents($f), true);
    if (!is_array($d)) { echo "skip $name (invalid JSON)\n"; continue; }
    echo (save_json($name, $d) ? 'imported ' : 'FAILED ') . "$name\n";
}

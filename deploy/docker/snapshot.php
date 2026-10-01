<?php
// Consistent copy of the live SQLite database for backups: php /opt/panel-market/snapshot.php <target>
$db = getenv('FARVAM_DB') ?: '/var/lib/panel-market/panel-market.sqlite';
$out = $argv[1] ?? '/var/lib/panel-market/snapshot.sqlite';
@unlink($out);
$pdo = new PDO('sqlite:' . $db, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA busy_timeout = 10000');
$pdo->exec('VACUUM INTO ' . $pdo->quote($out));
echo "snapshot: $out (" . filesize($out) . " bytes)\n";

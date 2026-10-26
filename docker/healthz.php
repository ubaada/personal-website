<?php
$cronPid = (int) @file_get_contents('/run/website/cron.pid');
if ($cronPid <= 0 || !is_dir('/proc/' . $cronPid)) {
    http_response_code(503);
    exit('scheduler unavailable');
}
$database = getenv('DATABASE_PATH') ?: __DIR__ . '/../sqlite/data.db';
if (!is_file($database) || !is_readable($database)) {
    http_response_code(503);
    exit('database unavailable');
}
try {
    $db = new SQLite3($database, SQLITE3_OPEN_READONLY);
    if ($db->querySingle("SELECT count(*) FROM sqlite_master WHERE type='table' AND name IN ('posts', 'users')") !== 2) {
        throw new RuntimeException('Missing application schema');
    }
    $db->close();
    echo 'ok';
} catch (Throwable $error) {
    http_response_code(503);
    echo 'database unavailable';
}

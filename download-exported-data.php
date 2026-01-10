<?php
/**
 * Download Exported SQL File
 * Downloads the exported sample-data-localhost.sql file
 *
 * ⚠️ DELETE THIS FILE AFTER DOWNLOADING!
 */

$filename = __DIR__ . '/sql/sample-data-localhost.sql';

if (!file_exists($filename)) {
    die("Error: File not found! Please run export-tidb-data.php first.");
}

// Set headers to force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="sample-data-localhost.sql"');
header('Content-Length: ' . filesize($filename));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

// Output file
readfile($filename);
exit;
?>

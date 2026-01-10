<?php
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Path Check</title><style>body{font-family:monospace;padding:20px;background:#f3f4f6}</style></head><body>";
echo "<h1>Path Configuration Check</h1>";
echo "<pre>";
echo "APP_URL: " . APP_URL . "\n";
echo "BASE_PATH: " . BASE_PATH . "\n";
echo "\nServer Variables:\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "\nExpected CSS URL:\n";
echo APP_URL . "/assets/css/main.css\n";
echo "\nTest this URL in browser - does it load?\n";
echo "</pre>";
echo "</body></html>";
?>

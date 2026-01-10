<?php
/**
 * Export TiDB Data to SQL Files
 * Exports current database state to create fresh sample data files
 *
 * ⚠️ DELETE THIS FILE AFTER RUNNING!
 */

set_time_limit(300);
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Export TiDB Data</title><style>body{font-family:system-ui;max-width:1200px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:10px;background:#d1fae5;border-radius:5px;margin:10px 0}.error{color:#dc2626;padding:10px;background:#fee2e2;border-radius:5px;margin:10px 0}.info{color:#0284c7;padding:10px;background:#e0f2fe;border-radius:5px;margin:10px 0}pre{background:#f3f4f6;padding:15px;border-radius:5px;overflow-x:auto;font-size:12px;max-height:400px;overflow-y:auto}</style></head><body>";

echo "<h1>📦 Export TiDB Data to SQL Files</h1>";

try {
    // Tables to export
    $tables = [
        'users',
        'projects',
        'project_members',
        'tasks',
        'task_comments',
        'task_attachments',
        'activity_log',
        'password_resets'
    ];

    $sqlOutput = "-- TaskFlow Sample Data\n";
    $sqlOutput .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlOutput .= "-- Source: TiDB Cloud Database\n\n";
    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $totalRows = 0;

    foreach ($tables as $table) {
        echo "<h2>Exporting: {$table}</h2>";

        // Get row count
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
        $count = $countStmt->fetch()['count'];
        $totalRows += $count;

        if ($count === 0) {
            echo "<div class='info'>⚠️ Table is empty, skipping...</div>";
            continue;
        }

        echo "<div class='success'>Found {$count} rows</div>";

        // Get all data
        $stmt = $pdo->query("SELECT * FROM `{$table}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate INSERT statements
        $sqlOutput .= "-- Table: {$table} ({$count} rows)\n";
        $sqlOutput .= "TRUNCATE TABLE `{$table}`;\n";

        if (!empty($rows)) {
            // Get column names from first row
            $columns = array_keys($rows[0]);
            $columnsList = '`' . implode('`, `', $columns) . '`';

            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                $valuesList = implode(', ', $values);
                $sqlOutput .= "INSERT INTO `{$table}` ({$columnsList}) VALUES ({$valuesList});\n";
            }
        }

        $sqlOutput .= "\n";
        flush();
    }

    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    // Save to file
    $filename = __DIR__ . '/sql/sample-data-localhost.sql';
    $backupFilename = __DIR__ . '/sql/sample-data-localhost-backup-' . date('Y-m-d-His') . '.sql';

    // Backup existing file
    if (file_exists($filename)) {
        copy($filename, $backupFilename);
        echo "<div class='success'>✅ Backed up existing file to: " . basename($backupFilename) . "</div>";
    }

    // Write new file
    file_put_contents($filename, $sqlOutput);

    echo "<div class='success'>";
    echo "<h2>✅ Export Complete!</h2>";
    echo "<p><strong>Total rows exported:</strong> {$totalRows}</p>";
    echo "<p><strong>File saved to:</strong> sql/sample-data-localhost.sql</p>";
    echo "<p><strong>File size:</strong> " . number_format(strlen($sqlOutput)) . " bytes</p>";
    if (file_exists($backupFilename)) {
        echo "<p><strong>Backup saved to:</strong> " . basename($backupFilename) . "</p>";
    }
    echo "</div>";

    // Show preview
    echo "<div class='info'>";
    echo "<h3>File Preview (first 50 lines)</h3>";
    $lines = explode("\n", $sqlOutput);
    $preview = implode("\n", array_slice($lines, 0, 50));
    echo "<pre>" . htmlspecialchars($preview) . "</pre>";
    if (count($lines) > 50) {
        echo "<p><em>... and " . (count($lines) - 50) . " more lines</em></p>";
    }
    echo "</div>";

    // Summary table
    echo "<div class='info'>";
    echo "<h3>Export Summary</h3>";
    echo "<table style='width:100%;border-collapse:collapse'>";
    echo "<tr style='background:#2563eb;color:white'><th style='padding:10px;border:1px solid #ddd'>Table</th><th style='padding:10px;border:1px solid #ddd'>Rows Exported</th></tr>";

    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
        $count = $countStmt->fetch()['count'];
        $bgColor = $count > 0 ? '#d1fae5' : '#f3f4f6';
        echo "<tr style='background:{$bgColor}'><td style='padding:10px;border:1px solid #ddd'><strong>{$table}</strong></td><td style='padding:10px;border:1px solid #ddd'>{$count}</td></tr>";
    }
    echo "</table>";
    echo "</div>";

    echo "<div class='success'>";
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Download the new <code>sql/sample-data-localhost.sql</code> file from Render</li>";
    echo "<li>Or copy the file content from the preview above</li>";
    echo "<li>Replace your local <code>sql/sample-data-localhost.sql</code> with this version</li>";
    echo "<li>Commit and push to GitHub</li>";
    echo "<li>Run the password fix script to set all users to 'password123'</li>";
    echo "</ol>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e'>";
echo "<h3>⚠️ IMPORTANT</h3>";
echo "<p><strong>DELETE this file after exporting:</strong> export-tidb-data.php</p>";
echo "</div>";

echo "</body></html>";
?>

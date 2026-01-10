<?php
/**
 * Complete Database Import - Adds Missing Tables and Data
 * This will create missing tables (comments, attachments) and ensure all data is imported
 *
 * ⚠️ DELETE THIS FILE AFTER RUNNING!
 */

set_time_limit(300);
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Complete Import</title><style>body{font-family:system-ui;max-width:900px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:10px;background:#d1fae5;border-radius:5px;margin:10px 0}.error{color:#dc2626;padding:10px;background:#fee2e2;border-radius:5px;margin:10px 0}.info{color:#0284c7;padding:10px;background:#e0f2fe;border-radius:5px;margin:10px 0}.warning{background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e}pre{background:#f3f4f6;padding:10px;border-radius:5px;font-size:12px}</style></head><body>";

echo "<h1>📦 Complete Database Import</h1>";
echo "<p>Creating missing tables and importing all data...</p>";

flush();

// Step 1: Create missing tables
echo "<h2>Step 1: Create Missing Tables</h2>";

$schemaFile = __DIR__ . '/sql/database-localhost.sql';
$schemaSql = file_get_contents($schemaFile);
$schemaSql = preg_replace('/--.*$/m', '', $schemaSql);
$schemaSql = preg_replace('/\/\*.*?\*\//s', '', $schemaSql);
$statements = array_filter(array_map('trim', explode(';', $schemaSql)));

foreach ($statements as $statement) {
    if (empty($statement)) continue;

    if (stripos($statement, 'CREATE TABLE') !== false) {
        preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
        $tableName = $matches[1] ?? 'unknown';

        try {
            $pdo->exec($statement);
            echo "<div class='success'>✅ Created table: <strong>{$tableName}</strong></div>";
            flush();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "<div class='info'>ℹ️ Table <strong>{$tableName}</strong> already exists</div>";
            } else {
                echo "<div class='error'>❌ Error creating {$tableName}: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

// Step 2: Import ALL data (will skip duplicates)
echo "<h2>Step 2: Import Sample Data</h2>";

$dataFile = __DIR__ . '/sql/sample-data-localhost.sql';
$dataSql = file_get_contents($dataFile);
$dataSql = preg_replace('/--.*$/m', '', $dataSql);
$dataSql = preg_replace('/\/\*.*?\*\//s', '', $dataSql);

// Split statements more carefully to handle multi-line INSERTs
$statements = [];
$currentStatement = '';
$inInsert = false;

foreach (explode("\n", $dataSql) as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    if (stripos($line, 'INSERT INTO') !== false) {
        if ($currentStatement) {
            $statements[] = $currentStatement;
        }
        $currentStatement = $line;
        $inInsert = true;
    } else {
        $currentStatement .= ' ' . $line;
    }

    if ($inInsert && strpos($line, ';') !== false) {
        $statements[] = rtrim($currentStatement, ';');
        $currentStatement = '';
        $inInsert = false;
    }
}

if ($currentStatement) {
    $statements[] = rtrim($currentStatement, ';');
}

echo "<div class='info'>Found " . count($statements) . " INSERT statements</div>";

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;

    if (stripos($statement, 'INSERT INTO') !== false) {
        preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
        $tableName = $matches[1] ?? 'unknown';

        try {
            $result = $pdo->exec($statement);
            $rows = $result > 0 ? $result : 1;
            echo "<div class='success'>✅ Inserted into <strong>{$tableName}</strong></div>";
            flush();
        } catch (PDOException $e) {
            // Skip duplicate key errors silently
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "<div class='error'>❌ Error in {$tableName}: " . htmlspecialchars(substr($e->getMessage(), 0, 100)) . "...</div>";
            }
        }
    }
}

// Step 3: Verification
echo "<h2>Step 3: Verification</h2>";

$tables = [
    'users', 'projects', 'project_members', 'tasks',
    'task_comments', 'task_attachments', 'activity_log', 'password_resets'
];

echo "<table style='width:100%;border-collapse:collapse;margin:20px 0'>";
echo "<tr style='background:#2563eb;color:white'><th style='padding:10px;border:1px solid #ddd'>Table</th><th style='padding:10px;border:1px solid #ddd'>Row Count</th></tr>";

$totalRows = 0;
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
        $count = $stmt->fetch()['count'];
        $totalRows += $count;
        $bgColor = $count > 0 ? '#d1fae5' : '#fee2e2';
        echo "<tr style='background:{$bgColor}'><td style='padding:10px;border:1px solid #ddd'><strong>{$table}</strong></td><td style='padding:10px;border:1px solid #ddd'>{$count}</td></tr>";
    } catch (PDOException $e) {
        echo "<tr style='background:#fee2e2'><td style='padding:10px;border:1px solid #ddd'><strong>{$table}</strong></td><td style='padding:10px;border:1px solid #ddd'>❌ Missing</td></tr>";
    }
}
echo "</table>";

echo "<div class='success'><h3>✅ Total: {$totalRows} rows in database</h3></div>";

// Check admin user's projects
echo "<h2>Step 4: Admin User Project Access</h2>";

try {
    $adminProjects = $pdo->prepare("
        SELECT p.id, p.name, pm.role
        FROM projects p
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = 1
        ORDER BY p.id
    ");
    $adminProjects->execute();
    $projects = $adminProjects->fetchAll();

    if (count($projects) > 0) {
        echo "<div class='success'>";
        echo "<h3>✅ Admin has access to " . count($projects) . " projects:</h3>";
        echo "<ul>";
        foreach ($projects as $project) {
            echo "<li><strong>{$project['name']}</strong> (Role: {$project['role']})</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Admin has NO project access!</h3>";
        echo "<p>Need to add admin to project_members table</p>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>Error checking projects: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<div class='success'>";
echo "<h2>🎉 Import Complete!</h2>";
echo "<p><a href='dashboard/index.php' style='background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;display:inline-block;font-weight:bold'>Go to Dashboard</a></p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>⚠️ IMPORTANT</h3>";
echo "<p><strong>DELETE these files now:</strong></p>";
echo "<ul>";
echo "<li>complete-import.php</li>";
echo "<li>fix-password.php</li>";
echo "<li>check-database.php</li>";
echo "<li>check-paths.php</li>";
echo "<li>test-db.php</li>";
echo "<li>import-database.php</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>

<?php
/**
 * One-Click Database Import Script
 * This will create all tables and import sample data to TiDB Cloud
 *
 * ⚠️ DELETE THIS FILE AFTER SUCCESSFUL IMPORT!
 */

set_time_limit(300); // 5 minutes max
require_once 'config/config.php';

// Security check - only allow if no users exist yet
try {
    $checkStmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $checkStmt->fetch();
    if ($result && $result['count'] > 0) {
        die("<h2>❌ Database Already Has Data!</h2><p>Found {$result['count']} users. This script only runs on empty databases to prevent data loss.</p><p>If you want to re-import, manually delete all data first using TiDB Cloud console.</p>");
    }
} catch (PDOException $e) {
    // Table doesn't exist yet, which is fine - we'll create it
}

echo "<!DOCTYPE html><html><head><title>Database Import</title><style>body{font-family:system-ui;max-width:800px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:10px;background:#d1fae5;border-radius:5px;margin:10px 0}.error{color:#dc2626;padding:10px;background:#fee2e2;border-radius:5px;margin:10px 0}.info{color:#0284c7;padding:10px;background:#e0f2fe;border-radius:5px;margin:10px 0}pre{background:#f3f4f6;padding:15px;border-radius:5px;overflow-x:auto}</style></head><body>";

echo "<h1>📦 TaskFlow Database Import</h1>";
echo "<p>Importing database schema and sample data to TiDB Cloud...</p>";

flush();

// Step 1: Create Tables
echo "<h2>Step 1: Creating Tables</h2>";

$schemaFile = __DIR__ . '/sql/database-localhost.sql';
if (!file_exists($schemaFile)) {
    die("<div class='error'>❌ Schema file not found: {$schemaFile}</div>");
}

$schemaSql = file_get_contents($schemaFile);

// Remove comments and split by semicolon
$schemaSql = preg_replace('/--.*$/m', '', $schemaSql); // Remove -- comments
$schemaSql = preg_replace('/\/\*.*?\*\//s', '', $schemaSql); // Remove /* */ comments
$statements = array_filter(array_map('trim', explode(';', $schemaSql)));

$tableCount = 0;
foreach ($statements as $statement) {
    if (empty($statement)) continue;

    try {
        $pdo->exec($statement);
        if (stripos($statement, 'CREATE TABLE') !== false) {
            preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
            $tableName = $matches[1] ?? 'unknown';
            echo "<div class='success'>✅ Created table: <strong>{$tableName}</strong></div>";
            $tableCount++;
            flush();
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') === false) {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

echo "<div class='info'>📊 Created {$tableCount} tables</div>";

// Step 2: Import Sample Data
echo "<h2>Step 2: Importing Sample Data</h2>";

$dataFile = __DIR__ . '/sql/sample-data-localhost.sql';
if (!file_exists($dataFile)) {
    die("<div class='error'>❌ Data file not found: {$dataFile}</div>");
}

$dataSql = file_get_contents($dataFile);

// Remove comments and split by semicolon
$dataSql = preg_replace('/--.*$/m', '', $dataSql);
$dataSql = preg_replace('/\/\*.*?\*\//s', '', $dataSql);
$statements = array_filter(array_map('trim', explode(';', $dataSql)));

$insertCount = 0;
foreach ($statements as $statement) {
    if (empty($statement)) continue;

    try {
        $result = $pdo->exec($statement);
        if (stripos($statement, 'INSERT INTO') !== false) {
            preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
            $tableName = $matches[1] ?? 'unknown';
            $rows = $result > 0 ? $result : 1;
            echo "<div class='success'>✅ Inserted {$rows} row(s) into <strong>{$tableName}</strong></div>";
            $insertCount++;
            flush();
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo "<div class='info'>📝 Executed {$insertCount} INSERT statements</div>";

// Step 3: Verify Import
echo "<h2>Step 3: Verification</h2>";

try {
    $usersStmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $usersStmt->fetch()['count'];

    $projectsStmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $projectsCount = $projectsStmt->fetch()['count'];

    $tasksStmt = $pdo->query("SELECT COUNT(*) as count FROM tasks");
    $tasksCount = $tasksStmt->fetch()['count'];

    echo "<div class='success'>";
    echo "<h3>✅ Import Successful!</h3>";
    echo "<ul>";
    echo "<li><strong>Users:</strong> {$usersCount}</li>";
    echo "<li><strong>Projects:</strong> {$projectsCount}</li>";
    echo "<li><strong>Tasks:</strong> {$tasksCount}</li>";
    echo "</ul>";
    echo "</div>";

    // Show demo credentials
    echo "<div class='info'>";
    echo "<h3>🔑 Demo Login Credentials</h3>";
    echo "<p><strong>Email:</strong> admin@taskflow.com<br>";
    echo "<strong>Password:</strong> password123</p>";
    echo "</div>";

    // Test password hash
    $adminStmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $adminStmt->execute(['admin@taskflow.com']);
    $admin = $adminStmt->fetch();

    if ($admin) {
        $testPassword = 'password123';
        $isValid = password_verify($testPassword, $admin['password']);

        if ($isValid) {
            echo "<div class='success'>✅ Password verification test: PASSED</div>";
        } else {
            echo "<div class='error'>❌ Password verification test: FAILED</div>";
        }
    }

} catch (PDOException $e) {
    echo "<div class='error'>❌ Verification error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<div style='background:#fef3c7;padding:20px;border-radius:5px;margin-top:30px'>";
echo "<h3>⚠️ IMPORTANT: Next Steps</h3>";
echo "<ol>";
echo "<li><strong>Test Login:</strong> Go to <a href='auth/login.php'>Login Page</a> and try logging in with admin@taskflow.com / password123</li>";
echo "<li><strong>DELETE THIS FILE:</strong> This script should only be run once. Delete <code>import-database.php</code> and <code>test-db.php</code> from your repository for security!</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>

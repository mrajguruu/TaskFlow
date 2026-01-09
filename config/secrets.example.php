<?php
/**
 * Secrets Configuration Template
 *
 * Copy this file to secrets.php and fill in your actual values:
 * cp secrets.example.php secrets.php
 *
 * IMPORTANT: secrets.php is gitignored and should NEVER be committed to version control
 */

return [
    // ============================================================================
    // Cleanup Token (64-character hexadecimal string)
    // ============================================================================
    // Generate with: openssl rand -hex 32
    // Or PHP: php -r "echo bin2hex(random_bytes(32));"
    //
    // For Render.com: Uses CLEANUP_TOKEN environment variable
    // For Localhost: Set token manually below
    'cleanup_token' => getenv('CLEANUP_TOKEN') ?: 'GENERATE_YOUR_OWN_64_CHARACTER_TOKEN_HERE',

    // ============================================================================
    // Protected User IDs
    // ============================================================================
    // These user IDs will NEVER be deleted by the cleanup script
    // By default, protects demo accounts (users 1-8)
    'protected_user_ids' => [1, 2, 3, 4, 5, 6, 7, 8],

    // ============================================================================
    // Cleanup Age (Hours)
    // ============================================================================
    // Users older than this will be deleted (in hours)
    // Default: 1 hour (for testing environments)
    // Production: Consider 24 or 72 hours
    'cleanup_age_hours' => 1,

    // ============================================================================
    // Rate Limiting
    // ============================================================================
    // Minimum seconds between cleanup runs (prevents abuse)
    // Default: 600 seconds (10 minutes)
    'min_interval_seconds' => 600,

    // ============================================================================
    // Log File Path
    // ============================================================================
    // Where to store cleanup operation logs
    'log_file' => __DIR__ . '/../logs/cleanup.log',

    // ============================================================================
    // IP Whitelist (Optional)
    // ============================================================================
    // Leave empty to allow from any IP (recommended for GitHub Actions)
    // Or specify allowed IPs/ranges:
    // 'allowed_ips' => ['185.199.108.0/22', '140.82.112.0/20'],
    'allowed_ips' => [],

    // ============================================================================
    // Additional Security Settings
    // ============================================================================

    // Enable detailed logging (useful for debugging)
    'enable_detailed_logging' => true,

    // Maximum users to delete in one run (safety limit)
    'max_delete_per_run' => 100,
];

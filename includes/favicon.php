<?php
/**
 * Favicon Include
 * Include this file in the <head> section of all pages
 * Usage: <?php require_once '../includes/favicon.php'; ?>
 */

$faviconPath = defined('APP_URL') ? APP_URL . '/assets/images/favicon' : '../assets/images/favicon';
?>
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $faviconPath ?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $faviconPath ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $faviconPath ?>/favicon-16x16.png">
    <link rel="manifest" href="<?= $faviconPath ?>/site.webmanifest">
    <link rel="shortcut icon" href="<?= $faviconPath ?>/favicon.ico">

    <!-- Theme Color for mobile browsers -->
    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-config" content="<?= $faviconPath ?>/browserconfig.xml">

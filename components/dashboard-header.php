<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/responsive.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/professional-theme.css')) ?>">
</head>
<body>
<div class="dashboard-shell">
    <?php require __DIR__ . '/dashboard-sidebar.php'; ?>
    <button class="dashboard-overlay" type="button" aria-label="Close dashboard menu"></button>
    <section class="dashboard-main">
        <header class="dashboard-topbar">
            <button class="dashboard-menu-toggle button button-secondary button-compact" type="button" aria-label="Toggle dashboard menu" aria-expanded="false">☰ Menu</button>
            <div class="dashboard-topbar-title"><span><?= e($pageTitle) ?></span></div>
            <div class="dashboard-topbar-actions">
                <a href="<?= e(url('index.php')) ?>">View Website</a>
                <span class="dashboard-user"><?= e(current_user_name()) ?> · <?= e(ucfirst(current_user_type())) ?></span>
            </div>
        </header>
        <main class="dashboard-content">
            <?php require __DIR__ . '/flash-message.php'; ?>

<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GearBridge is a peer-to-peer campus tool and equipment library for students and staff.">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/responsive.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/professional-theme.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
<main>
<?php require __DIR__ . '/flash-message.php'; ?>

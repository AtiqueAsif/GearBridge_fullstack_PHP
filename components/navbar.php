<?php
$currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
$listItemUrl = is_logged_in() ? url('dashboard/add-item.php') : url('login.php');
?>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand brand-mark" href="<?= e(url('index.php')) ?>" aria-label="GearBridge home">
            <span class="brand-icon" aria-hidden="true">GB</span>
            <span class="brand-text"><span>Gear</span><em>Bridge</em></span>
        </a>

        <button class="menu-toggle button button-secondary button-compact" type="button" aria-label="Toggle navigation" aria-expanded="false">Menu</button>

        <nav class="main-nav" aria-label="Primary navigation">
            <a class="<?= $currentPage === 'index.php' ? 'is-active' : '' ?>" href="<?= e(url('index.php')) ?>">Home</a>
            <a class="<?= $currentPage === 'browse.php' || $currentPage === 'item-details.php' ? 'is-active' : '' ?>" href="<?= e(url('browse.php')) ?>">Browse</a>
            <a href="<?= e($listItemUrl) ?>">List an Item</a>
            <a href="<?= e(url('index.php#how-it-works')) ?>">How It Works</a>
            <a href="<?= e(url('index.php#faq')) ?>">FAQ</a>
            <a class="<?= $currentPage === 'about.php' ? 'is-active' : '' ?>" href="<?= e(url('about.php')) ?>">About</a>
            <?php if (is_logged_in()): ?>
                <a class="button button-small" href="<?= e(url('dashboard/index.php')) ?>">Dashboard</a>
            <?php else: ?>
                <a class="button button-small" href="<?= e(url('login.php')) ?>">Sign In</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php
require_once __DIR__ . '/../includes/csrf.php';
$currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
$links = [
    'index.php' => ['⌂', 'Dashboard', 'dashboard/index.php'],
    'my-items.php' => ['▦', 'My Items', 'dashboard/my-items.php'],
    'add-item.php' => ['＋', 'Add Item', 'dashboard/add-item.php'],
    'my-requests.php' => ['↗', 'My Requests', 'dashboard/my-requests.php'],
    'incoming-requests.php' => ['↙', 'Incoming Requests', 'dashboard/incoming-requests.php'],
    'active-borrowings.php' => ['⇄', 'Active Borrowings', 'dashboard/active-borrowings.php'],
    'borrow-history.php' => ['◷', 'Borrow History', 'dashboard/borrow-history.php'],
    'profile.php' => ['◎', 'Profile', 'dashboard/profile.php'],
];
?>
<aside class="dashboard-sidebar" aria-label="Dashboard navigation">
    <a class="brand dashboard-brand" href="<?= e(url('dashboard/index.php')) ?>">
        <span class="brand-icon" aria-hidden="true">GB</span>
        <span class="brand-text"><span>Gear</span><em>Bridge</em></span>
    </a>

    <nav>
        <?php foreach ($links as $file => [$icon, $label, $path]): ?>
            <?php $active = $currentPage === $file || ($currentPage === 'edit-item.php' && $file === 'my-items.php'); ?>
            <a class="<?= $active ? 'is-active' : '' ?>" href="<?= e(url($path)) ?>">
                <span class="nav-icon" aria-hidden="true"><?= e($icon) ?></span>
                <span><?= e($label) ?></span>
            </a>
        <?php endforeach; ?>

        <form action="<?= e(url('actions/auth/logout.php')) ?>" method="post">
            <?= csrf_field() ?>
            <button class="link-button" type="submit"><span class="nav-icon" aria-hidden="true">↪</span><span>Logout</span></button>
        </form>
    </nav>
</aside>

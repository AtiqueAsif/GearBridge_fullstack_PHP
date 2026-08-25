<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$pageTitle = 'Profile';
$user = null;

try {
    $stmt = db()->prepare(
        'SELECT id, full_name, email, user_type, created_at FROM users WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => current_user_id()]);
    $user = $stmt->fetch();
} catch (Throwable $e) {
    set_flash('error', 'Your profile could not be loaded.');
}

if (!$user) {
    set_flash('error', 'Your account could not be found.');
    redirect('index.php');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel form-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Account</p>
            <h1>My Profile</h1>
            <p class="panel-copy">Keep your basic account information current.</p>
        </div>
    </div>

    <form action="<?= e(url('actions/profile/update.php')) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-grid two-col">
            <label for="full_name">Full Name
                <input id="full_name" name="full_name" maxlength="100" required value="<?= e($user['full_name']) ?>">
            </label>

            <label for="email">Email
                <input id="email" name="email" type="email" maxlength="150" required value="<?= e($user['email']) ?>">
            </label>

            <label>User Type
                <input value="<?= e(ucfirst((string) $user['user_type'])) ?>" disabled>
                <span class="field-hint">User type is selected at registration and is not editable here.</span>
            </label>

            <label>Joined
                <input value="<?= e(format_date((string) $user['created_at'])) ?>" disabled>
            </label>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Save Profile</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_guest();

$pageTitle = 'Login';
$old = pull_form_data();
require __DIR__ . '/components/public-header.php';
?>
<section class="auth-section">
    <div class="auth-shell">
        <div class="auth-visual">
            <p class="eyebrow">Campus sharing made simple</p>
            <h2>Share more.<br><span>Buy less.</span></h2>
            <p>Access your dashboard to list equipment, review requests, borrow useful resources and keep the full borrowing lifecycle organized.</p>
        </div>
        <div class="auth-card">
            <p class="eyebrow">Welcome back</p>
            <h1>Sign in</h1>
            <p class="form-intro">Use your GearBridge account to manage listed items and borrowing activity.</p>

            <form action="<?= e(url('actions/auth/login.php')) ?>" method="post">
                <?= csrf_field() ?>

                <label for="email">Email
                    <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= e((string) ($old['email'] ?? '')) ?>" placeholder="name@example.com">
                </label>

                <label for="password">Password
                    <input id="password" name="password" type="password" maxlength="255" autocomplete="current-password" required placeholder="Enter your password">
                </label>

                <button class="button button-block" type="submit">Sign In</button>
            </form>

            <div class="demo-login-panel" aria-label="Demo login credentials">
                <strong>Demo access</strong>
                <p>Try <code>student01@demo.com</code> or <code>staff01@demo.com</code></p>
                <p>Password: <code>Demo@123</code></p>
            </div>

            <p class="auth-switch">No account yet? <a href="<?= e(url('register.php')) ?>">Create one</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/components/public-footer.php'; ?>

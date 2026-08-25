<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_guest();

$pageTitle = 'Register';
$old = pull_form_data();
require __DIR__ . '/components/public-header.php';
?>
<section class="auth-section">
    <div class="auth-shell">
        <div class="auth-visual">
            <p class="eyebrow">Join the campus community</p>
            <h2>Borrow smart.<br><span>Share useful gear.</span></h2>
            <p>Create a student or staff account to list underused equipment, discover available resources and manage borrowing requests securely.</p>
        </div>
        <div class="auth-card">
            <p class="eyebrow">Create your account</p>
            <h1>Register</h1>
            <p class="form-intro">Join GearBridge as a student or staff member.</p>

            <form action="<?= e(url('actions/auth/register.php')) ?>" method="post">
                <?= csrf_field() ?>

                <label for="full_name">Full Name
                    <input id="full_name" name="full_name" type="text" maxlength="100" autocomplete="name" required value="<?= e((string) ($old['full_name'] ?? '')) ?>" placeholder="Your full name">
                </label>

                <label for="email">Email
                    <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= e((string) ($old['email'] ?? '')) ?>" placeholder="name@example.com">
                </label>

                <label for="user_type">User Type
                    <select id="user_type" name="user_type" required>
                        <option value="">Select user type</option>
                        <option value="student" <?= (($old['user_type'] ?? '') === 'student') ? 'selected' : '' ?>>Student</option>
                        <option value="staff" <?= (($old['user_type'] ?? '') === 'staff') ? 'selected' : '' ?>>Staff</option>
                    </select>
                </label>

                <div class="form-grid two-col">
                    <label for="password">Password
                        <input id="password" name="password" type="password" minlength="8" maxlength="255" autocomplete="new-password" required placeholder="Minimum 8 characters">
                    </label>

                    <label for="password_confirmation">Confirm Password
                        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="255" autocomplete="new-password" required placeholder="Repeat password">
                    </label>
                </div>

                <button class="button button-block" type="submit">Create Account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="<?= e(url('login.php')) ?>">Sign in</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/components/public-footer.php'; ?>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <strong>GearBridge</strong>
            <p>A peer-to-peer campus tool and equipment library for students and staff — built around access, reuse and smarter sharing.</p>
        </div>
        <div class="footer-links">
            <a href="<?= e(url('index.php')) ?>">Home</a>
            <a href="<?= e(url('browse.php')) ?>">Browse</a>
            <a href="<?= e(is_logged_in() ? url('dashboard/add-item.php') : url('login.php')) ?>">List an Item</a>
            <a href="<?= e(url('index.php#how-it-works')) ?>">How It Works</a>
            <a href="<?= e(url('about.php')) ?>">About</a>
            <?php if (!is_logged_in()): ?>
                <a href="<?= e(url('register.php')) ?>">Register</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container footer-bottom">GearBridge · CSE 3120 Web Programming · Peer-to-peer campus resource sharing.</div>
</footer>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>

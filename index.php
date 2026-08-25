<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Home';

$items = [];
$categories = [];
$stats = ['available_items' => 0, 'members' => 0];

try {
    $pdo = db();

    $stmt = $pdo->query(
        "SELECT i.id, i.title, i.condition_status, i.availability_status, i.image_path, c.name AS category_name
         FROM items i
         INNER JOIN categories c ON c.id = i.category_id
         WHERE i.deleted_at IS NULL AND i.availability_status = 'available'
         ORDER BY i.created_at DESC
         LIMIT 4"
    );
    $items = $stmt->fetchAll();

    $categories = $pdo->query(
        "SELECT c.id, c.name,
                COUNT(CASE WHEN i.deleted_at IS NULL AND i.availability_status = 'available' THEN 1 END) AS available_count
         FROM categories c
         LEFT JOIN items i ON i.category_id = c.id
         GROUP BY c.id, c.name
         ORDER BY c.id"
    )->fetchAll();

    $stats['available_items'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM items WHERE deleted_at IS NULL AND availability_status = 'available'"
    )->fetchColumn();
    $stats['members'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Throwable $e) {
    $items = [];
    $categories = [];
}

require __DIR__ . '/components/public-header.php';
?>
<section class="hero">
    <span class="hero-pattern pattern-a" aria-hidden="true"></span>
    <span class="hero-pattern pattern-b" aria-hidden="true"></span>
    <div class="container hero-inner">
        <div class="hero-copy">
            <p class="eyebrow">Peer-to-peer campus sharing</p>
            <h1 class="hero-title" aria-label="GearBridge">
                <span class="word-gear">Gear</span>
                <span class="word-bridge">Bridge</span>
            </h1>
            <p class="hero-tagline">Borrow what you need</p>
            <p class="hero-description">A peer-to-peer campus tool &amp; equipment library for students and staff. Share cameras, lab tools, electronics kits, textbooks and other useful resources instead of buying everything new.</p>

            <div class="hero-actions">
                <a class="button" href="<?= e(url('browse.php')) ?>">⌕ Browse Items</a>
                <a class="button button-secondary" href="<?= e(is_logged_in() ? url('dashboard/add-item.php') : url('login.php')) ?>">＋ List an Item</a>
            </div>

            <div class="hero-stats" aria-label="Platform summary">
                <div><strong><?= (int) $stats['available_items'] ?></strong><span>Available Items</span></div>
                <div><strong><?= (int) $stats['members'] ?></strong><span>Campus Members</span></div>
            </div>
        </div>

        <div class="hero-visual" aria-label="Campus equipment including camera, textbooks, lab electronics and laptop">
            <img class="hero-equipment-image" src="<?= e(url('assets/images/hero-equipment.png')) ?>" alt="Camera, textbooks, electronics kits, tools and laptop representing GearBridge equipment">
            <div class="hero-visual-badge">Share useful gear. Borrow smarter. Waste less.</div>
        </div>
    </div>
</section>

<section class="trust-strip" aria-label="Platform benefits">
    <div class="container trust-grid">
        <div class="trust-item"><span class="trust-icon">◎</span><div><strong>Peer to Peer</strong><span>Campus community</span></div></div>
        <div class="trust-item"><span class="trust-icon">✓</span><div><strong>Account Based</strong><span>Tracked requests</span></div></div>
        <div class="trust-item"><span class="trust-icon">♻</span><div><strong>Share More</strong><span>Waste less</span></div></div>
    </div>
</section>

<section class="search-band" aria-label="Search equipment">
    <div class="container">
        <form class="hero-search" action="<?= e(url('browse.php')) ?>" method="get">
            <label class="sr-only" for="home-search">Search shared equipment</label>
            <input id="home-search" name="q" type="search" maxlength="100" placeholder="Search cameras, lab tools, electronics, textbooks...">
            <button class="button" type="submit">Search Equipment</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Available now</p>
                <h2>Featured <span class="section-title-accent">Equipment</span></h2>
                <p>Browse recently shared items that are currently available to request.</p>
            </div>
            <a class="text-link" href="<?= e(url('browse.php')) ?>">View all equipment →</a>
        </div>

        <?php if ($items): ?>
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <?php require __DIR__ . '/components/item-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No equipment listed yet</h3>
                <p>The first available items will appear here after campus members add them.</p>
                <?php if (is_logged_in()): ?>
                    <a class="button" href="<?= e(url('dashboard/add-item.php')) ?>">List the First Item</a>
                <?php else: ?>
                    <a class="button" href="<?= e(url('register.php')) ?>">Join GearBridge</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section dark-section" id="how-it-works">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">How it works</p>
                <h2>Simple sharing. <span class="section-title-accent">Three steps.</span></h2>
                <p>GearBridge keeps the core workflow clear: list, request, borrow and return.</p>
            </div>
        </div>

        <div class="step-grid">
            <article>
                <span class="step-number">01</span>
                <h3>List an item</h3>
                <p>Add an underused camera, lab tool, electronics kit, textbook or other useful equipment to the campus library.</p>
            </article>
            <article>
                <span class="step-number">02</span>
                <h3>Send a request</h3>
                <p>Browse available resources, choose the dates you need, and send the owner a borrowing request through your account.</p>
            </article>
            <article>
                <span class="step-number">03</span>
                <h3>Borrow &amp; return</h3>
                <p>The owner approves the request, the item becomes unavailable, and after return the owner marks it available again.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Explore</p>
                <h2>Browse by <span class="section-title-accent">Category</span></h2>
                <p>Jump directly to the type of resource you need.</p>
            </div>
        </div>

        <?php if ($categories): ?>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <a class="category-card" href="<?= e(page_url('browse.php', ['category' => (int) $category['id']])) ?>">
                        <span><?= e($category['name']) ?></span>
                        <strong><?= (int) $category['available_count'] ?> available</strong>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section sustainability-section">
    <div class="container sustainability-grid">
        <div>
            <p class="eyebrow">Why share?</p>
            <h2>Share more.<br>Waste less.</h2>
        </div>
        <div class="prose">
            <p>Useful equipment often sits unused while someone else on campus needs the same resource for a short period. Shared access helps reduce unnecessary purchasing and keeps existing resources in active use.</p>
            <p>GearBridge supports a localized circular economy: students and staff can save money, make better use of resources, and contribute to reducing electronic and material waste.</p>
            <a class="text-link" href="<?= e(url('about.php')) ?>">Learn about the project →</a>
        </div>
    </div>
</section>

<section class="section" id="faq">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Quick answers</p>
                <h2>GearBridge <span class="section-title-accent">FAQ</span></h2>
            </div>
        </div>
        <div class="faq-grid">
            <details open><summary>Who can use GearBridge?</summary><p>The platform is designed for students and staff. Registered users can list equipment, send requests and manage borrowing activity.</p></details>
            <details><summary>Can I borrow my own item?</summary><p>No. The system prevents users from sending a borrowing request for equipment they own.</p></details>
            <details><summary>What happens after an item is returned?</summary><p>The owner confirms the physical return. The borrowing record becomes returned and the item becomes available again.</p></details>
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="container final-cta-card">
        <div>
            <p class="eyebrow">Ready to participate?</p>
            <h2><?= is_logged_in() ? 'Have equipment you are not using?' : 'Join the campus sharing community.' ?></h2>
        </div>
        <a class="button" href="<?= e(is_logged_in() ? url('dashboard/add-item.php') : url('register.php')) ?>">
            <?= is_logged_in() ? '＋ List an Item' : 'Create Account' ?>
        </a>
    </div>
</section>

<?php require __DIR__ . '/components/public-footer.php'; ?>

<?php

declare(strict_types=1);

$pageTitle = 'About';
require __DIR__ . '/components/public-header.php';
?>
<section class="page-hero">
    <div class="container narrow">
        <p class="eyebrow">About GearBridge</p>
        <h1>Shared access to useful campus resources.</h1>
        <p>GearBridge is a peer-to-peer tool and equipment library designed for students and staff. It helps campus members list underused resources, discover what others have shared, and manage borrowing requests through one database-driven platform.</p>
    </div>
</section>

<section class="section">
    <div class="container content-grid">
        <article>
            <span class="feature-number">01</span>
            <h2>The problem</h2>
            <p>Useful equipment can remain unused for long periods while other campus members may need the same resources for short-term academic work.</p>
        </article>
        <article>
            <span class="feature-number">02</span>
            <h2>The approach</h2>
            <p>Students and staff list equipment, browse available resources, and manage borrowing requests without turning the platform into a commercial marketplace.</p>
        </article>
        <article>
            <span class="feature-number">03</span>
            <h2>The benefit</h2>
            <p>Shared access can reduce unnecessary purchases, support better resource use, lower costs, and contribute to reducing electronic and material waste.</p>
        </article>
    </div>
</section>

<section class="section dark-section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Designed around access</p>
                <h2>A simple peer-to-peer workflow.</h2>
                <p>Every listed item has a clear owner, condition and availability state. Requests and returns are tracked through authenticated user accounts.</p>
            </div>
        </div>
        <div class="step-grid">
            <article><span class="step-number">01</span><h3>List &amp; discover</h3><p>Owners list underused equipment while other campus members browse what is available.</p></article>
            <article><span class="step-number">02</span><h3>Request &amp; approve</h3><p>Borrowers request an item for a date range and the owner approves or rejects the request.</p></article>
            <article><span class="step-number">03</span><h3>Return &amp; reopen</h3><p>After physical return, the owner confirms the return and the item becomes available again.</p></article>
        </div>
    </div>
</section>

<section class="section sustainability-section">
    <div class="container sustainability-grid">
        <div>
            <p class="eyebrow">Circular campus thinking</p>
            <h2>Use more.<br>Buy less.</h2>
        </div>
        <div class="prose">
            <p>GearBridge is intentionally focused on temporary access rather than commercial transactions. It helps useful equipment stay active instead of sitting unused.</p>
            <p>The platform supports better resource utilization, student savings, reduced unnecessary consumption and a localized circular economy.</p>
            <a class="text-link" href="<?= e(url('browse.php')) ?>">Explore equipment →</a>
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="container final-cta-card">
        <div>
            <p class="eyebrow">Explore the library</p>
            <h2>See what the campus community has shared.</h2>
        </div>
        <a class="button" href="<?= e(url('browse.php')) ?>">Browse Equipment</a>
    </div>
</section>

<?php require __DIR__ . '/components/public-footer.php'; ?>

<?php $flash = pull_flash(); ?>
<?php if ($flash): ?>
    <div class="flash-wrap" aria-live="polite">
        <div class="alert alert-<?= e((string) ($flash['type'] ?? 'info')) ?>" role="status">
            <?= e((string) ($flash['message'] ?? '')) ?>
        </div>
    </div>
<?php endif; ?>

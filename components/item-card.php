<?php
$item = $item ?? [];
$image = !empty($item['image_path']) ? url((string) $item['image_path']) : url('assets/images/items/default-item.jpg');
$status = (string) ($item['availability_status'] ?? 'available');
?>
<article class="item-card">
    <img src="<?= e($image) ?>" alt="<?= e((string) ($item['title'] ?? 'Equipment')) ?>" loading="lazy">
    <div class="item-card-body">
        <p class="item-category"><?= e((string) ($item['category_name'] ?? 'Equipment')) ?></p>
        <h3><?= e((string) ($item['title'] ?? 'Untitled item')) ?></h3>
        <p>Condition: <?= e(ucfirst((string) ($item['condition_status'] ?? 'unknown'))) ?></p>
        <span class="status-badge <?= e(status_class($status)) ?>"><?= e(status_label($status)) ?></span>
        <?php if (!empty($item['id'])): ?>
            <a class="button button-block" href="<?= e(url('item-details.php?id=' . (int) $item['id'])) ?>">View Details →</a>
        <?php endif; ?>
    </div>
</article>

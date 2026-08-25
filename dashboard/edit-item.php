<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';
require_login();

$itemId = positive_int($_GET['id'] ?? null);
if ($itemId === null) {
    set_flash('error', 'Invalid equipment link.');
    redirect('dashboard/my-items.php');
}

$item = null;
$categories = [];

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT * FROM items WHERE id = :id AND owner_id = :owner_id AND deleted_at IS NULL LIMIT 1'
    );
    $stmt->execute(['id' => $itemId, 'owner_id' => current_user_id()]);
    $item = $stmt->fetch();

    if (!$item) {
        set_flash('error', 'You are not authorized to edit this item.');
        redirect('dashboard/my-items.php');
    }

    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'The item could not be loaded.');
    redirect('dashboard/my-items.php');
}

$pageTitle = 'Edit Item';
require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel form-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Equipment management</p>
            <h1>Edit Item</h1>
            <p class="panel-copy">Availability is controlled automatically by the borrowing workflow.</p>
        </div>
        <span class="status-badge <?= e(status_class((string) $item['availability_status'])) ?>"><?= e(status_label((string) $item['availability_status'])) ?></span>
    </div>

    <form action="<?= e(url('actions/items/update.php')) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">

        <div class="form-grid two-col">
            <label for="title">Item Name
                <input id="title" name="title" maxlength="150" required value="<?= e($item['title']) ?>">
            </label>

            <label for="category_id">Category
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= ((int) $item['category_id'] === (int) $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="condition_status">Condition
                <select id="condition_status" name="condition_status" required>
                    <?php foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ((string) $item['condition_status'] === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="image">Replace Image
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input>
                <span class="field-hint">Leave empty to keep the current image.</span>
            </label>
        </div>

        <label for="description">Description
            <textarea id="description" name="description" rows="7" maxlength="5000" required><?= e($item['description']) ?></textarea>
        </label>

        <div class="edit-image-row">
            <div>
                <span class="field-hint">Current image</span>
                <?php $currentImage = $item['image_path'] ? url((string) $item['image_path']) : url('assets/images/items/default-item.jpg'); ?>
                <img class="image-preview" src="<?= e($currentImage) ?>" alt="Current item image">
            </div>
            <div class="image-preview-wrap" data-image-preview-wrap hidden>
                <span class="field-hint">New image preview</span>
                <img data-image-preview class="image-preview" alt="Selected item preview">
            </div>
        </div>

        <div class="form-actions">
            <a class="button button-secondary" href="<?= e(url('dashboard/my-items.php')) ?>">Cancel</a>
            <button class="button" type="submit">Save Changes</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>

<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$pageTitle = 'Add Item';
$categories = [];
$old = pull_form_data();

try {
    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Categories could not be loaded. Check the database setup.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel form-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Share equipment</p>
            <h1>Add New Item</h1>
            <p class="panel-copy">Provide clear information so other campus members can understand what you are sharing.</p>
        </div>
    </div>

    <form action="<?= e(url('actions/items/create.php')) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-grid two-col">
            <label for="title">Item Name
                <input id="title" name="title" maxlength="150" required value="<?= e($old['title'] ?? '') ?>" placeholder="e.g. Arduino Starter Kit">
            </label>

            <label for="category_id">Category
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= ((string) ($old['category_id'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="condition_status">Condition
                <select id="condition_status" name="condition_status" required>
                    <option value="">Select condition</option>
                    <?php foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= (($old['condition_status'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="image">Item Image
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input>
                <span class="field-hint">Optional. JPG, PNG or WEBP, up to 5 MB.</span>
            </label>
        </div>

        <label for="description">Description
            <textarea id="description" name="description" rows="7" maxlength="5000" required placeholder="Describe the item, what is included, and any important handling notes."><?= e($old['description'] ?? '') ?></textarea>
        </label>

        <div class="image-preview-wrap" data-image-preview-wrap hidden>
            <span class="field-hint">Image preview</span>
            <img data-image-preview class="image-preview" alt="Selected item preview">
        </div>

        <div class="form-actions">
            <a class="button button-secondary" href="<?= e(url('dashboard/my-items.php')) ?>">Cancel</a>
            <button class="button" type="submit">Add Item</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>

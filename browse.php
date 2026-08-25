<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/validation.php';

$pageTitle = 'Browse Equipment';

$search = trim((string) ($_GET['q'] ?? ''));
if (text_length($search) > 100) {
    $search = text_substr($search, 0, 100);
}

$category = positive_int($_GET['category'] ?? null);
$condition = (string) ($_GET['condition'] ?? '');
$availability = (string) ($_GET['availability'] ?? 'available');
$page = positive_int($_GET['page'] ?? 1) ?? 1;

$allowedConditions = ['excellent', 'good', 'fair'];
$allowedAvailability = ['available', 'borrowed', 'all'];

if (!in_array($condition, $allowedConditions, true)) {
    $condition = '';
}
if (!in_array($availability, $allowedAvailability, true)) {
    $availability = 'available';
}

$perPage = 12;
$totalItems = 0;
$totalPages = 1;
$items = [];
$categories = [];

try {
    $pdo = db();
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

    $where = ['i.deleted_at IS NULL'];
    $params = [];

    if ($search !== '') {
        $where[] = '(i.title LIKE :search_title OR i.description LIKE :search_description)';
        $params['search_title'] = '%' . $search . '%';
        $params['search_description'] = '%' . $search . '%';
    }
    if ($category !== null) {
        $where[] = 'i.category_id = :category';
        $params['category'] = $category;
    }
    if ($condition !== '') {
        $where[] = 'i.condition_status = :condition';
        $params['condition'] = $condition;
    }
    if ($availability !== 'all') {
        $where[] = 'i.availability_status = :availability';
        $params['availability'] = $availability;
    }

    $whereSql = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM items i WHERE {$whereSql}");
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT i.id, i.title, i.condition_status, i.availability_status, i.image_path,
                   c.name AS category_name
            FROM items i
            INNER JOIN categories c ON c.id = i.category_id
            WHERE {$whereSql}
            ORDER BY i.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (Throwable $e) {
    $items = [];
    $categories = [];
    $totalItems = 0;
    $totalPages = 1;
}

$baseQuery = [
    'q' => $search,
    'category' => $category,
    'condition' => $condition,
    'availability' => $availability,
];

require __DIR__ . '/components/public-header.php';
?>
<section class="page-hero section-soft">
    <div class="container">
        <p class="eyebrow">Equipment library</p>
        <h1>Browse shared equipment</h1>
        <p>Find tools and resources shared by students and staff across the campus community.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('browse.php')) ?>">
            <label class="sr-only" for="q">Search equipment</label>
            <input id="q" type="search" name="q" value="<?= e($search) ?>" placeholder="Search equipment">

            <label class="sr-only" for="category">Category</label>
            <select id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= $category === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label class="sr-only" for="condition">Condition</label>
            <select id="condition" name="condition">
                <option value="">Any condition</option>
                <?php foreach ($allowedConditions as $value): ?>
                    <option value="<?= e($value) ?>" <?= $condition === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
                <?php endforeach; ?>
            </select>

            <label class="sr-only" for="availability">Availability</label>
            <select id="availability" name="availability">
                <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="borrowed" <?= $availability === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                <option value="all" <?= $availability === 'all' ? 'selected' : '' ?>>All</option>
            </select>

            <button class="button" type="submit">Search</button>
            <a class="button button-secondary" href="<?= e(url('browse.php')) ?>">Clear</a>
        </form>

        <div class="results-summary">
            <strong><?= (int) $totalItems ?></strong> equipment item<?= $totalItems === 1 ? '' : 's' ?> found
        </div>

        <?php if ($items): ?>
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <?php require __DIR__ . '/components/item-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Equipment pages">
                    <?php if ($page > 1): ?>
                        <a href="<?= e(page_url('browse.php', $baseQuery + ['page' => $page - 1])) ?>">← Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($p = $start; $p <= $end; $p++):
                    ?>
                        <a class="<?= $p === $page ? 'is-active' : '' ?>" href="<?= e(page_url('browse.php', $baseQuery + ['page' => $p])) ?>"><?= $p ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= e(page_url('browse.php', $baseQuery + ['page' => $page + 1])) ?>">Next →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>No equipment found</h2>
                <p>Try changing your search or filters.</p>
                <a class="button button-secondary" href="<?= e(url('browse.php')) ?>">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/components/public-footer.php'; ?>

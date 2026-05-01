<?php
// items/browse.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ItemFactory.php';

$pageTitle = 'Browse Items';
require_once '../includes/header.php';

$itemDAO = new ItemDAO();
$search_query = trim($_GET['q'] ?? '');

$filters = [
    'type' => $_GET['type'] ?? '',
    'category_id' => $_GET['category_id'] ?? '',
    'location_id' => $_GET['location_id'] ?? ''
];

$itemsData = $itemDAO->getAllOpenItems($search_query, $filters);
$categories = $itemDAO->getCategories();
$locations = $itemDAO->getLocations();

?>

<div class="page-wrapper">
    <div class="container" style="display: flex; gap: 2rem; align-items: flex-start;">
        
        <!-- Sidebar Filtering -->
        <aside style="width: 280px; flex-shrink: 0; background: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); position: sticky; top: 100px;">
            <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--gray-light); padding-bottom: 0.5rem;">Filters</h3>
            <form method="GET" action="browse.php" id="filterForm">
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-weight: 500; font-size: 0.9rem; margin-bottom: 0.3rem; display: block;">Search Keyword</label>
                    <input type="text" name="q" class="form-control" placeholder="e.g. iPhone" value="<?= htmlspecialchars($search_query) ?>" style="font-size: 0.9rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-weight: 500; font-size: 0.9rem; margin-bottom: 0.3rem; display: block;">Item Type</label>
                    <select name="type" class="form-control" style="font-size: 0.9rem;">
                        <option value="">All Types</option>
                        <option value="lost" <?= $filters['type'] === 'lost' ? 'selected' : '' ?>>Lost Items</option>
                        <option value="found" <?= $filters['type'] === 'found' ? 'selected' : '' ?>>Found Items</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-weight: 500; font-size: 0.9rem; margin-bottom: 0.3rem; display: block;">Category</label>
                    <select name="category_id" class="form-control" style="font-size: 0.9rem;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $filters['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 500; font-size: 0.9rem; margin-bottom: 0.3rem; display: block;">Location</label>
                    <select name="location_id" class="form-control" style="font-size: 0.9rem;">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= $loc['location_id'] ?>" <?= $filters['location_id'] == $loc['location_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc['location_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Apply Filters</button>
                <a href="browse.php" class="btn btn-outline btn-full" style="margin-top: 0.5rem; text-decoration: none; text-align: center; display: block;">Clear All</a>
            </form>
        </aside>

        <!-- Main Content -->
        <main style="flex: 1;">
            <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>CampusClaim Items</h1>
                <a href="report.php" class="btn btn-primary">Report an Item</a>
            </div>

            <?php if (empty($itemsData)): ?>
                <div class="alert alert-info">No open items found matching your search or filters.</div>
            <?php else: ?>
                <div class="item-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($itemsData as $data): ?>
                        <?php $itemObj = ItemFactory::createItem($data); ?>
                        <div class="item-card" style="background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 1.5rem; transition: transform 0.2s;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1.2rem;"><?= htmlspecialchars($itemObj->getTitle()) ?></h3>
                                <span class="badge badge-<?= $itemObj->getType() === 'lost' ? 'danger' : 'success' ?>" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: <?= $itemObj->getType() === 'lost' ? '#fee2e2' : '#dcfce7' ?>; color: <?= $itemObj->getType() === 'lost' ? '#991b1b' : '#166534' ?>;">
                                    <?= ucfirst($itemObj->getType()) ?>
                                </span>
                            </div>
                            <p class="item-meta" style="font-size: 0.85rem; color: #666; margin: 0.5rem 0;">
                                <strong>Category:</strong> <?= htmlspecialchars($data['category_name']) ?><br>
                                <strong>Location:</strong> <?= htmlspecialchars($data['location_name']) ?><br>
                                <strong>Date:</strong> <?= htmlspecialchars($itemObj->getItemDate()) ?>
                            </p>
                            <p style="font-size: 0.9rem; line-height: 1.4; color: #444;"><?= htmlspecialchars(substr($itemObj->getDescription(), 0, 80)) ?>...</p>
                            <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--gray-light);">
                            <a href="details.php?id=<?= $itemObj->getId() ?>" class="btn btn-outline btn-full" style="text-align: center; display: block; text-decoration: none;">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

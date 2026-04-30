<?php
// items/browse.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ItemFactory.php';

$itemDAO = new ItemDAO();
$search_query = trim($_GET['q'] ?? '');
$itemsData = $itemDAO->getAllOpenItems($search_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Items | Ashesi Lost & Found</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        .item-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 1.5rem;
            transition: transform 0.2s;
        }
        .item-card:hover {
            transform: translateY(-5px);
        }
        .item-meta {
            font-size: 0.85rem;
            color: #666;
            margin: 0.5rem 0;
        }
        .badge-lost { background-color: #ffebee; color: #c62828; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; }
        .badge-found { background-color: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header-actions">
            <h1>Lost & Found Items</h1>
            <div>
                <a href="report.php" class="btn btn-primary">Report an Item</a>
                <a href="../auth/login.php" class="btn btn-outline" style="margin-left: 10px;">Logout</a>
            </div>
        </div>

        <form method="GET" action="browse.php" style="margin-bottom: 2rem; display: flex; gap: 1rem;">
            <input type="text" name="q" class="form-control" placeholder="Search by title or description (e.g., 'iPhone', 'Keys')..." value="<?= htmlspecialchars($search_query) ?>" style="flex: 1; padding: 0.8rem; font-size: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0 2rem;">Search</button>
            <?php if (!empty($search_query)): ?>
                <a href="browse.php" class="btn btn-outline" style="padding: 0.8rem 1.5rem; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($itemsData)): ?>
            <div class="alert alert-info">No open items found matching your search.</div>
        <?php else: ?>
            <div class="item-grid">
                <?php foreach ($itemsData as $data): ?>
                    <?php 
                        // Use Factory to create object (demonstrating OOP usage)
                        $itemObj = ItemFactory::createItem($data); 
                    ?>
                    <div class="item-card">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <h3><?= htmlspecialchars($itemObj->getTitle()) ?></h3>
                            <?= $itemObj->getDisplayBadge() ?>
                        </div>
                        <p class="item-meta">
                            <strong>Category:</strong> <?= htmlspecialchars($data['category_name']) ?><br>
                            <strong>Location:</strong> <?= htmlspecialchars($data['location_name']) ?><br>
                            <strong>Date:</strong> <?= htmlspecialchars($itemObj->getItemDate()) ?>
                        </p>
                        <p><?= htmlspecialchars(substr($itemObj->getDescription(), 0, 100)) ?>...</p>
                        <hr style="margin: 1rem 0; border: none; border-top: 1px solid #eee;">
                        <a href="details.php?id=<?= $itemObj->getId() ?>" class="btn btn-outline btn-full" style="text-align: center; display: block; text-decoration: none;">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

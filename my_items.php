<?php
require_once 'classes/Database.php';
$pdo = Database::getInstance()->getConnection();

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once 'classes/ItemDAO.php';
require_once 'classes/ItemFactory.php';
require_once 'classes/MatchingStrategy.php';
require_once 'classes/AIConfig.php';
require_once 'classes/SemanticMatchingStrategy.php';

$itemDAO  = new ItemDAO();
$strategy = AIConfig::enabled() ? new SemanticMatchingStrategy() : new FlexibleMatchingStrategy();
$userId   = $_SESSION['user_id'];
$success  = '';
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    if ($itemId) {
        if ($itemDAO->deleteItem($itemId, $userId)) {
            $success = "Item deleted successfully.";
        } else {
            $error = "Failed to delete item. It may have already been claimed.";
        }
    }
}

$myItemsRaw = $itemDAO->getItemsByUserId($userId);
$myItems = [];
foreach ($myItemsRaw as $itemData) {
    $itemObj = ItemFactory::createItem($itemData);
    $itemObj->setId($itemData['item_id']);
    $matches = $strategy->findMatches($itemObj, $itemDAO);
    $itemData['match_count'] = count($matches);
    $myItems[] = $itemData;
}

$pageTitle = 'My Items';
require_once 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">

        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>My Items</h1>
                <p class="text-muted">Manage the items you have reported.</p>
            </div>
            <a href="items/report.php" class="btn btn-primary">Report New Item</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($myItems)): ?>
            <div style="text-align: center; padding: 4rem; background: var(--white); border-radius: 8px; box-shadow: var(--shadow);">
                <p class="text-muted" style="margin-bottom: 1rem;">You haven't reported any items yet.</p>
                <a href="items/report.php" class="btn btn-outline">Get Started</a>
            </div>
        <?php else: ?>
            <div style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--gray-light); border-bottom: 2px solid var(--gray);">
                            <th style="padding: 1rem;">Type</th>
                            <th style="padding: 1rem;">Title</th>
                            <th style="padding: 1rem;">Date Reported</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myItems as $item): ?>
                            <tr style="border-bottom: 1px solid var(--gray-light);">
                                <td style="padding: 1rem;">
                                    <span style="font-size: 0.8rem; padding: 0.2rem 0.5rem; border-radius: 4px;
                                        background: <?= $item['type'] === 'lost' ? '#fee2e2' : '#dcfce7' ?>;
                                        color: <?= $item['type'] === 'lost' ? '#991b1b' : '#166534' ?>;">
                                        <?= ucfirst($item['type']) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-weight: 500;">
                                    <a href="items/details.php?id=<?= $item['item_id'] ?>" style="color: var(--crimson); text-decoration: none;">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </td>
                                <td style="padding: 1rem; color: var(--text-light);">
                                    <?= date('M d, Y', strtotime($item['created_at'])) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="font-weight: 500; color: <?= $item['status'] === 'open' ? 'var(--gold)' : 'var(--green)' ?>;">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                                        <?php if ($item['status'] === 'open' && $item['match_count'] > 0): ?>
                                            <a href="items/matches.php?id=<?= $item['item_id'] ?>" class="btn" 
                                               style="background: var(--gold); color: white; padding: 0.4rem 0.8rem; font-size: 0.85rem; text-decoration: none; border-radius: 4px;">
                                               View Matches (<?= $item['match_count'] ?>)
                                            </a>
                                        <?php endif; ?>

                                        <form method="POST" action="my_items.php" style="display: inline-block;"
                                              onsubmit="return confirm('Delete this report? This cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <button type="submit" class="btn" style="background: #fee2e2; color: #991b1b; padding: 0.4rem 0.8rem; font-size: 0.85rem; border: none; border-radius: 4px; cursor: pointer;">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

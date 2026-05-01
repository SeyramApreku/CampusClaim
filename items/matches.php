<?php
// items/matches.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ItemFactory.php';
require_once '../classes/MatchingStrategy.php';

if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$item_id = $_GET['id'];
$itemDAO = new ItemDAO();
$data = $itemDAO->getItemById($item_id);

if (!$data || $data['user_id'] !== $_SESSION['user_id']) {
    die("You can only view matches for your own reported items.");
}

$itemObj = ItemFactory::createItem($data);

// Re-run the matching algorithm to get the results for the view
$strategy = new ExactLocationCategoryStrategy();
$matches = $strategy->findMatches($itemObj, $itemDAO);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potential Matches | CampusClaim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .match-banner {
            background-color: #e3f2fd;
            border-left: 5px solid #2196f3;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 4px;
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
        }
        .badge-lost { background-color: #ffebee; color: #c62828; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; }
        .badge-found { background-color: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; }
    </style>
</head>
<body style="background-color: #f4f7f6;">
    <div class="container">
        <a href="browse.php" class="btn btn-outline" style="margin-bottom: 1rem; display: inline-block;">&larr; Skip and go to Dashboard</a>
        
        <div class="match-banner">
            <h2>🎉 Great News! We found <?= count($matches) ?> potential match(es)!</h2>
            <p style="color: #444; margin-top: 0.5rem;">
                Based on your report of a <strong><?= htmlspecialchars($itemObj->getTitle()) ?></strong> 
                in <strong><?= htmlspecialchars($data['location_name']) ?></strong>, 
                our matching algorithm found these items. Are any of these yours?
            </p>
        </div>

        <div class="item-grid">
            <?php foreach ($matches as $matchData): ?>
                <?php $matchObj = ItemFactory::createItem($matchData); ?>
                <div class="item-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <h3><?= htmlspecialchars($matchObj->getTitle()) ?></h3>
                        <?= $matchObj->getDisplayBadge() ?>
                    </div>
                    <p style="color: #666; font-size: 0.9rem; margin: 0.5rem 0;">
                        Reported on: <?= htmlspecialchars($matchObj->getItemDate()) ?>
                    </p>
                    <p><?= htmlspecialchars(substr($matchObj->getDescription(), 0, 100)) ?>...</p>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #eee;">
                    <a href="details.php?id=<?= $matchObj->getId() ?>" class="btn btn-primary btn-full" style="text-align: center; display: block; text-decoration: none;">View Details to Claim</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

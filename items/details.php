<?php
// items/details.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ItemFactory.php';
require_once '../classes/ClaimDAO.php';

if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$item_id = $_GET['id'];
$itemDAO = new ItemDAO();
$data = $itemDAO->getItemById($item_id);

if (!$data) {
    die("Item not found.");
}

$itemObj = ItemFactory::createItem($data);
$claimDAO = new ClaimDAO();
$hasClaimed = $claimDAO->hasUserClaimedItem($_SESSION['user_id'], $item_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($itemObj->getTitle()) ?> | CampusClaim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .details-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1.5rem 0;
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .badge-lost { background-color: #ffebee; color: #c62828; padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 1rem; }
        .badge-found { background-color: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 1rem; }
    </style>
</head>
<body style="background-color: #f4f7f6;">
    <div class="details-container">
        <a href="browse.php" style="color: #666; text-decoration: none;">&larr; Back to Dashboard</a>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <h1><?= htmlspecialchars($itemObj->getTitle()) ?></h1>
            <?= $itemObj->getDisplayBadge() ?>
        </div>

        <div class="meta-grid">
            <div><strong>Reported By:</strong> <?= htmlspecialchars($data['reporter_name']) ?></div>
            <div><strong>Date:</strong> <?= htmlspecialchars($itemObj->getItemDate()) ?></div>
            <div><strong>Category:</strong> <?= htmlspecialchars($data['category_name']) ?></div>
            <div><strong>Location:</strong> <?= htmlspecialchars($data['location_name']) ?></div>
        </div>

        <h3>Description</h3>
        <p style="line-height: 1.6; color: #444;"><?= nl2br(htmlspecialchars($itemObj->getDescription())) ?></p>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #eee;">

        <div style="text-align: center;">
            <?php if ($itemObj->getType() === 'found' && $itemObj->getUserId() !== $_SESSION['user_id']): ?>
                <?php if ($hasClaimed): ?>
                    <div class="alert alert-info">You have already submitted a claim for this item. Please wait for review.</div>
                <?php else: ?>
                    <a href="claim.php?id=<?= $itemObj->getId() ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.8rem 2rem;">I Think This Is Mine! (Claim)</a>
                <?php endif; ?>
            <?php elseif ($itemObj->getType() === 'lost' && $itemObj->getUserId() !== $_SESSION['user_id']): ?>
                <button class="btn btn-outline" onclick="alert('Messaging feature coming soon!')">Message Reporter</button>
            <?php else: ?>
                <p style="color: #666;"><em>You reported this item.</em></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

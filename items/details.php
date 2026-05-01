<?php
require_once '../classes/Database.php';
$pdo = Database::getInstance()->getConnection();

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

$item_id    = (int) $_GET['id'];
$itemDAO    = new ItemDAO();
$data       = $itemDAO->getItemById($item_id);

if (!$data) { header("Location: browse.php"); exit; }

$itemObj    = ItemFactory::createItem($data);
$claimDAO   = new ClaimDAO();
$hasClaimed = $claimDAO->hasUserClaimedItem($_SESSION['user_id'], $item_id);

$pageTitle = htmlspecialchars($itemObj->getTitle());
require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">

            <a href="browse.php" style="color: var(--text-light); text-decoration: none; font-size: 0.9rem;">&larr; Back to Browse</a>

            <?php if (isset($_GET['claimed'])): ?>
                <div class="alert alert-success" style="margin-top: 1rem;">Your claim has been submitted! An admin will review it shortly.</div>
            <?php endif; ?>

            <div style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); padding: 2rem; margin-top: 1.5rem;">

                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <h1 style="margin: 0; font-size: 1.6rem;"><?= htmlspecialchars($itemObj->getTitle()) ?></h1>
                    <?= $itemObj->getDisplayBadge() ?>
                </div>

                <?php if ($itemObj->getImageUrl()): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <img src="../<?= htmlspecialchars($itemObj->getImageUrl()) ?>" alt="<?= htmlspecialchars($itemObj->getTitle()) ?>"
                             style="max-width: 100%; border-radius: 8px; max-height: 350px; object-fit: cover;">
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: #f9fafb; padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <div><strong>Reported By:</strong> <?= htmlspecialchars($data['reporter_name']) ?></div>
                    <div><strong>Date:</strong> <?= htmlspecialchars($itemObj->getItemDate()) ?></div>
                    <div><strong>Category:</strong> <?= htmlspecialchars($data['category_name']) ?></div>
                    <div><strong>Location:</strong> <?= htmlspecialchars($data['location_name']) ?></div>
                    <div><strong>Status:</strong>
                        <span style="text-transform: capitalize; font-weight: 600; color: <?= $data['status'] === 'open' ? 'var(--gold)' : 'var(--green)' ?>;">
                            <?= htmlspecialchars($data['status']) ?>
                        </span>
                    </div>
                </div>

                <h3 style="margin-bottom: 0.5rem;">Description</h3>
                <p style="line-height: 1.7; color: #444;"><?= nl2br(htmlspecialchars($itemObj->getDescription())) ?></p>

                <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--gray-light);">

                <div style="text-align: center;">
                    <?php if ($itemObj->getType() === 'found' && $itemObj->getUserId() !== $_SESSION['user_id'] && $data['status'] === 'open'): ?>
                        <?php if ($hasClaimed): ?>
                            <div class="alert alert-info">You've already submitted a claim. Please wait for admin review.</div>
                        <?php else: ?>
                            <a href="claim.php?id=<?= $itemObj->getId() ?>" class="btn btn-primary" style="font-size: 1.05rem; padding: 0.8rem 2.5rem;">I Think This Is Mine &mdash; Claim It</a>
                        <?php endif; ?>
                    <?php elseif ($itemObj->getType() === 'lost' && $itemObj->getUserId() !== $_SESSION['user_id']): ?>
                        <button class="btn btn-outline" onclick="alert('Direct messaging is coming soon!')">Message Reporter</button>
                    <?php else: ?>
                        <p class="text-muted"><em>You reported this item.</em></p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

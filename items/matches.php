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
require_once '../classes/MatchingStrategy.php';
require_once '../classes/AIConfig.php';
require_once '../classes/SemanticMatchingStrategy.php';

if (!isset($_GET['id'])) { header("Location: browse.php"); exit; }

$item_id = (int) $_GET['id'];
$itemDAO = new ItemDAO();
$data    = $itemDAO->getItemById($item_id);

if (!$data || $data['user_id'] !== $_SESSION['user_id']) {
    header("Location: browse.php");
    exit;
}

$itemObj  = ItemFactory::createItem($data);
$itemObj->setId($item_id); // Ensure ID is set for matching logic
$strategy = AIConfig::enabled() ? new SemanticMatchingStrategy() : new FlexibleMatchingStrategy();
$matches  = $strategy->findMatches($itemObj, $itemDAO);

$pageTitle = 'Potential Matches';
require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">

        <div style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-left: 5px solid var(--crimson);
                    padding: 1.5rem 2rem; border-radius: 8px; margin-bottom: 2rem;">
            <h2 style="margin: 0 0 0.5rem;">We found <?= count($matches) ?> potential match<?= count($matches) !== 1 ? 'es' : '' ?>!</h2>
            <p style="margin: 0; color: #444;">
                Based on your report of <strong><?= htmlspecialchars($itemObj->getTitle()) ?></strong>
                in <strong><?= htmlspecialchars($data['location_name']) ?></strong>,
                our matching algorithm found these items. Check if any are yours.
            </p>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
            <a href="browse.php" class="btn btn-outline">&larr; Skip &mdash; Go to Browse</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($matches as $matchData): ?>
                <?php $matchObj = ItemFactory::createItem($matchData); ?>
                <div style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <h3 style="margin: 0; font-size: 1.1rem;"><?= htmlspecialchars($matchObj->getTitle()) ?></h3>
                        <?= $matchObj->getDisplayBadge() ?>
                    </div>
                    <p style="color: #666; font-size: 0.85rem; margin: 0 0 0.5rem;">
                        <strong>Reported on:</strong> <?= htmlspecialchars($matchObj->getItemDate()) ?><br>
                        <strong>Location:</strong> <?= htmlspecialchars($matchData['location_name']) ?>
                    </p>
                    <p style="font-size: 0.9rem; color: #444; line-height: 1.4;">
                        <?= htmlspecialchars(substr($matchObj->getDescription(), 0, 100)) ?>...
                    </p>
                    <?php if (isset($matchData['match_score'])): ?>
                        <p style="font-size: 0.85rem; color: #7a1f2b; background: #fff7ed; padding: 0.65rem; border-radius: 6px;">
                            <strong><?= (int) round($matchData['match_score'] * 100) ?>% potential match</strong><br>
                            <?= htmlspecialchars($matchData['match_explanation']) ?>
                        </p>
                    <?php endif; ?>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--gray-light);">
                    <a href="details.php?id=<?= $matchObj->getId() ?>" class="btn btn-primary btn-full"
                       style="text-align: center; display: block; text-decoration: none;">View Details &amp; Claim</a>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

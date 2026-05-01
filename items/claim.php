<?php
require_once '../classes/Database.php';
$pdo = Database::getInstance()->getConnection();

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ClaimDAO.php';

if (!isset($_GET['id'])) { header("Location: browse.php"); exit; }

$item_id  = (int) $_GET['id'];
$itemDAO  = new ItemDAO();
$data     = $itemDAO->getItemById($item_id);

if (!$data || $data['type'] !== 'found') { header("Location: browse.php"); exit; }

$claimDAO = new ClaimDAO();
if ($claimDAO->hasUserClaimedItem($_SESSION['user_id'], $item_id)) {
    header("Location: details.php?id=$item_id");
    exit;
}

$errors = [];
$proof  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proof = trim($_POST['proof_description'] ?? '');
    if (empty($proof)) {
        $errors[] = "Please provide proof of ownership.";
    }
    if (empty($errors)) {
        $claim = new Claim(['item_id' => $item_id, 'user_id' => $_SESSION['user_id'], 'proof_description' => $proof]);
        if ($claimDAO->createClaim($claim)) {
            header("Location: details.php?id=$item_id&claimed=1");
            exit;
        } else {
            $errors[] = "Failed to submit claim. Please try again.";
        }
    }
}

$pageTitle = 'Claim Item';
require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div style="max-width: 640px; margin: 0 auto;">

            <a href="details.php?id=<?= $item_id ?>" style="color: var(--text-light); text-decoration: none; font-size: 0.9rem;">&larr; Back to Item</a>

            <div style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); padding: 2rem; margin-top: 1.5rem;">
                <h1 style="margin-top: 0; margin-bottom: 0.25rem;">Claim This Item</h1>
                <p class="text-muted" style="margin-bottom: 1.5rem;">
                    <strong><?= htmlspecialchars($data['title']) ?></strong><br>
                    Provide specific details that prove this item belongs to you — e.g., serial number, lock screen password, unique markings, or contents.
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.25rem;">
                        <?php foreach ($errors as $err) echo "<p style='margin:0'>$err</p>"; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Proof of Ownership <span style="color: var(--crimson);">*</span></label>
                        <textarea name="proof_description" class="form-control" required rows="6"
                                  placeholder="e.g. The phone has a cracked back cover, my name is written inside the case..."><?= htmlspecialchars($proof) ?></textarea>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Claim</button>
                        <a href="details.php?id=<?= $item_id ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

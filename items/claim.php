<?php
// items/claim.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ClaimDAO.php';

if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$item_id = $_GET['id'];
$itemDAO = new ItemDAO();
$data = $itemDAO->getItemById($item_id);

if (!$data || $data['type'] !== 'found') {
    die("You can only claim found items.");
}

$claimDAO = new ClaimDAO();
if ($claimDAO->hasUserClaimedItem($_SESSION['user_id'], $item_id)) {
    die("You have already claimed this item.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proof = trim($_POST['proof_description'] ?? '');

    if (empty($proof)) {
        $errors[] = "Please provide proof of ownership.";
    }

    if (empty($errors)) {
        $claim = new Claim([
            'item_id' => $item_id,
            'user_id' => $_SESSION['user_id'],
            'proof_description' => $proof
        ]);

        if ($claimDAO->createClaim($claim)) {
            // Redirect with success
            header("Location: details.php?id=$item_id&claimed=1");
            exit;
        } else {
            $errors[] = "Failed to submit claim. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item | CampusClaim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body style="background-color: #f4f7f6;">
    <div class="form-container">
        <h2>Claim: <?= htmlspecialchars($data['title']) ?></h2>
        <p style="color: #666; margin-bottom: 1.5rem;">To claim this item, please provide specific details proving it belongs to you (e.g., serial number, password, unique marks, lock screen wallpaper).</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Proof of Ownership</label>
                <textarea name="proof_description" class="form-control" required rows="5" placeholder="Describe the item specifically..." style="width: 100%; padding: 0.5rem;"></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Claim</button>
                <a href="details.php?id=<?= $item_id ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

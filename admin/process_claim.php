<?php
// admin/process_claim.php
session_start();

// Ensure the user is logged in AND is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

require_once '../classes/ClaimDAO.php';
require_once '../classes/ItemDAO.php';

$claim_id = $_POST['claim_id'] ?? null;
$item_id = $_POST['item_id'] ?? null;
$action = $_POST['action'] ?? '';

if (!$claim_id || !in_array($action, ['approve', 'reject', 'notify_library'])) {
    header("Location: dashboard.php?error=Invalid request.");
    exit;
}

$claimDAO = new ClaimDAO();
$itemDAO = new ItemDAO();
require_once '../classes/NotificationDAO.php';
$notifDAO = new NotificationDAO();

if ($action === 'approve') {
    // 1. Approve the claim
    $claimSuccess = $claimDAO->approveClaim($claim_id);
    
    // 2. Mark the item as 'claimed' so it disappears from the open feed
    $itemSuccess = true;
    if ($item_id) {
        $itemSuccess = $itemDAO->updateItemStatus($item_id, 'claimed');
    }

    if ($claimSuccess && $itemSuccess) {
        header("Location: dashboard.php?success=Claim approved successfully. The item has been marked as claimed.");
    } else {
        header("Location: dashboard.php?error=Failed to approve claim. Please check the database.");
    }
    
} elseif ($action === 'reject') {
    // Just reject the claim, item remains 'open'
    if ($claimDAO->rejectClaim($claim_id)) {
        header("Location: dashboard.php?success=Claim rejected successfully.");
    } else {
        header("Location: dashboard.php?error=Failed to reject claim.");
    }
} elseif ($action === 'notify_library') {
    // 1. Get the claimant's ID and item title
    $stmt = Database::getInstance()->getConnection()->prepare(
        "SELECT c.user_id, i.title FROM claims c JOIN items i ON c.item_id = i.item_id WHERE c.claim_id = ?"
    );
    $stmt->execute([$claim_id]);
    $claimData = $stmt->fetch();

    if ($claimData) {
        $msg = "Further investigation needed for your claim on '" . $claimData['title'] . "'. Please come to the Library to verify ownership.";
        $notifDAO->createNotification($claimData['user_id'], $msg, "my_items.php");
        header("Location: dashboard.php?success=Notification sent to claimant.");
    } else {
        header("Location: dashboard.php?error=Claim not found.");
    }
}
exit;
?>

<?php
// admin/dashboard.php
session_start();

// Ensure the user is logged in AND is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ClaimDAO.php';

$claimDAO = new ClaimDAO();
$pendingClaims = $claimDAO->getAllPendingClaims();

// Check for success messages from process_claim.php
$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Ashesi Lost & Found</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .claim-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .claim-table th, .claim-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .claim-table th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>
<body style="background-color: #f4f7f6;">
    <div class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Admin Dashboard</h1>
            <a href="../auth/login.php" class="btn btn-outline">Logout</a>
        </div>
        
        <p style="color: #666;">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>.</p>

        <?php if ($success_msg): ?>
            <div class="alert alert-success" style="margin-top: 1rem;"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-error" style="margin-top: 1rem;"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <h2 style="margin-top: 2rem; border-bottom: 2px solid #2196f3; padding-bottom: 0.5rem; display: inline-block;">Pending Claims</h2>

        <?php if (empty($pendingClaims)): ?>
            <div class="alert alert-info" style="margin-top: 1rem;">No pending claims to review. Great job!</div>
        <?php else: ?>
            <table class="claim-table">
                <thead>
                    <tr>
                        <th>Date Submitted</th>
                        <th>Item Claimed</th>
                        <th>Claimant</th>
                        <th>Proof of Ownership</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingClaims as $claim): ?>
                        <tr>
                            <td><?= htmlspecialchars($claim['created_at']) ?></td>
                            <td><strong><?= htmlspecialchars($claim['item_title']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($claim['claimant_name']) ?><br>
                                <small style="color: #666;"><?= htmlspecialchars($claim['claimant_email']) ?></small>
                            </td>
                            <td style="max-width: 300px;"><?= nl2br(htmlspecialchars($claim['proof_description'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <form method="POST" action="process_claim.php" style="display: inline;">
                                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                        <input type="hidden" name="item_id" value="<?= $claim['item_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to APPROVE this claim? The item will be marked as claimed.');">Approve</button>
                                    </form>

                                    <form method="POST" action="process_claim.php" style="display: inline;">
                                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-outline" style="padding: 0.5rem 1rem; color: #c62828; border-color: #c62828;" onclick="return confirm('Are you sure you want to REJECT this claim?');">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

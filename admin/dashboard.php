<?php
require_once '../classes/Database.php';
$pdo = Database::getInstance()->getConnection();

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ClaimDAO.php';

$claimDAO     = new ClaimDAO();
$pendingClaims = $claimDAO->getAllPendingClaims();
$success_msg  = $_GET['success'] ?? '';
$error_msg    = $_GET['error']   ?? '';

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">

        <div class="dashboard-header" style="margin-bottom: 2rem;">
            <h1>Admin Dashboard</h1>
            <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>. Review pending claims below.</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--gray-light);">Pending Claims</h2>

        <?php if (empty($pendingClaims)): ?>
            <div class="alert alert-info">No pending claims to review. Great job!</div>
        <?php else: ?>
            <div style="overflow-x: auto; background: var(--white); border-radius: 8px; box-shadow: var(--shadow);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb;">
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600;">Date Submitted</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600;">Item Claimed</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600;">Claimant</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600;">Proof of Ownership</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingClaims as $claim): ?>
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?= htmlspecialchars($claim['created_at']) ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;"><strong><?= htmlspecialchars($claim['item_title']) ?></strong></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;">
                                    <?= htmlspecialchars($claim['claimant_name']) ?><br>
                                    <small style="color: #666;"><?= htmlspecialchars($claim['claimant_email']) ?></small>
                                </td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee; max-width: 300px;"><?= nl2br(htmlspecialchars($claim['proof_description'])) ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <form method="POST" action="process_claim.php" style="display: inline;">
                                            <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                            <input type="hidden" name="item_id"  value="<?= $claim['item_id'] ?>">
                                            <input type="hidden" name="action"   value="approve">
                                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;"
                                                onclick="return confirm('Approve this claim? The item will be marked as claimed.');">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="process_claim.php" style="display: inline;">
                                            <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                            <input type="hidden" name="item_id"  value="<?= $claim['item_id'] ?>">
                                            <input type="hidden" name="action"   value="notify_library">
                                            <button type="submit" class="btn" style="padding: 0.5rem 1rem; background: #3b82f6; color: white;"
                                                onclick="return confirm('Send notification to claimant to visit the library?');">
                                                Request Library Visit
                                            </button>
                                        </form>
                                        <form method="POST" action="process_claim.php" style="display: inline;">
                                            <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                            <input type="hidden" name="action"   value="reject">
                                            <button type="submit" class="btn btn-outline" style="padding: 0.5rem 1rem; color: #c62828; border-color: #c62828;"
                                                onclick="return confirm('Reject this claim?');">
                                                Reject
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

<?php require_once '../includes/footer.php'; ?>

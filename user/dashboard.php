<?php
// user/dashboard.php
// Student Dashboard - Command Center

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';

$pageTitle = 'Dashboard';
require_once '../includes/header.php';

$userId = $_SESSION['user_id'];
$itemDAO = new ItemDAO();

$activeReportsCount = $itemDAO->getActiveReportsCount($userId);
$recoveredCount = $itemDAO->getRecoveredItemsCount($userId);
$recentActivity = $itemDAO->getRecentActivity($userId);
?>

<div class="page-wrapper">
    <div class="container">
        
        <div class="dashboard-header" style="margin-bottom: 2rem;">
            <h1>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
            <p class="text-muted">Here is what is happening with your items today.</p>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            
            <div class="stat-card" style="background: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); border-top: 4px solid var(--gold);">
                <h3>Active Reports</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: var(--crimson);"><?= $activeReportsCount ?></div>
                <p class="text-muted" style="font-size: 0.9rem;">Items currently open</p>
            </div>

            <div class="stat-card" style="background: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); border-top: 4px solid var(--green);">
                <h3>Items Recovered</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: var(--crimson);"><?= $recoveredCount ?></div>
                <p class="text-muted" style="font-size: 0.9rem;">Successfully returned</p>
            </div>

            <div class="stat-card" style="background: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); border-top: 4px solid var(--gray);">
                <h3>Quick Action</h3>
                <a href="../items/report.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Report a New Item</a>
            </div>

        </div>

        <div class="activity-section">
            <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--gray-light);">Recent Activity</h2>
            
            <?php if (empty($recentActivity)): ?>
                <div class="empty-state" style="text-align: center; padding: 3rem; background: var(--white); border-radius: 8px; border: 1px dashed var(--gray);">
                    <p class="text-muted">You haven't reported any items yet.</p>
                    <a href="../items/report.php" class="btn btn-outline-primary" style="margin-top: 1rem;">Make your first report</a>
                </div>
            <?php else: ?>
                <div class="activity-list" style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); overflow: hidden;">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-light); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong><?= htmlspecialchars($activity['title']) ?></strong>
                                <span class="badge badge-<?= $activity['type'] === 'lost' ? 'danger' : 'success' ?>" style="margin-left: 0.5rem; font-size: 0.8rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: <?= $activity['type'] === 'lost' ? '#fee2e2' : '#dcfce7' ?>; color: <?= $activity['type'] === 'lost' ? '#991b1b' : '#166534' ?>;">
                                    <?= ucfirst($activity['type']) ?>
                                </span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.85rem; font-weight: 500; color: <?= $activity['status'] === 'open' ? 'var(--gold)' : 'var(--green)' ?>;">
                                    <?= ucfirst($activity['status']) ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    <?= date('M d, Y', strtotime($activity['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div style="padding: 1rem; text-align: center; background: #f8fafc;">
                        <a href="my_items.php" style="color: var(--crimson); font-weight: 500; text-decoration: none;">View All My Items &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
// notifications.php
// Display all notifications for the logged-in user

require_once 'classes/Database.php';

// Set page title before including header
$pageTitle = 'Notifications';
require_once 'includes/header.php';

// Get database connection
$db = Database::getInstance();
$pdo = $db->getConnection();

$userId = $_SESSION['user_id'];

// Mark notification as read if requested
if (isset($_GET['mark'])) {
    $notifId = filter_input(INPUT_GET, 'mark', FILTER_VALIDATE_INT);
    if ($notifId) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->execute([$notifId, $userId]);
        
        // Redirect to the link if available
        $stmt = $pdo->prepare("SELECT link FROM notifications WHERE notification_id = ?");
        $stmt->execute([$notifId]);
        $link = $stmt->fetchColumn();
        
        if ($link) {
            header("Location: $link");
            exit;
        }
    }
}

// Mark all as read if requested
if (isset($_GET['mark_all'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    header("Location: notifications.php");
    exit;
}

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
?>

<div class="page-wrapper">
    <div class="container">
        
        <div class="dashboard-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Notifications</h1>
                <p class="text-muted"><?= $unreadCount ?> unread notification<?= $unreadCount !== 1 ? 's' : '' ?></p>
            </div>
            <?php if ($unreadCount > 0): ?>
                <a href="notifications.php?mark_all=1" class="btn btn-outline">Mark All as Read</a>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="empty-state" style="text-align: center; padding: 4rem; background: var(--white); border-radius: 8px; box-shadow: var(--shadow);">
                <i class="ph ph-bell" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>
                <p class="text-muted">No notifications yet</p>
                <p style="font-size: 0.9rem; color: var(--text-light);">You'll receive notifications when someone claims your item or matches are found.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list" style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); overflow: hidden;">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>" style="padding: 1.5rem; border-bottom: 1px solid var(--gray-light); <?= $notif['is_read'] ? '' : 'background: #f0f9ff;' ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                            <div style="flex: 1;">
                                <?php if (!$notif['is_read']): ?>
                                    <span style="display: inline-block; width: 8px; height: 8px; background: var(--crimson); border-radius: 50%; margin-right: 0.5rem;"></span>
                                <?php endif; ?>
                                <p style="margin: 0 0 0.5rem 0; <?= $notif['is_read'] ? 'color: var(--text-light);' : 'font-weight: 500;' ?>">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </p>
                                <p style="font-size: 0.85rem; color: var(--text-light); margin: 0;">
                                    <?= date('M d, Y g:ia', strtotime($notif['created_at'])) ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <?php if ($notif['link']): ?>
                                    <a href="notifications.php?mark=<?= $notif['notification_id'] ?>" class="btn btn-sm btn-primary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                                        View
                                    </a>
                                <?php endif; ?>
                                <?php if (!$notif['is_read']): ?>
                                    <a href="notifications.php?mark=<?= $notif['notification_id'] ?>" class="btn btn-sm btn-outline" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                                        Mark as Read
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

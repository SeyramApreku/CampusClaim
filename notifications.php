<?php
require_once 'classes/Database.php';
$pdo = Database::getInstance()->getConnection();

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Mark a single notification as read, then redirect cleanly
if (isset($_GET['mark'])) {
    $notifId = (int) $_GET['mark'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);
    header("Location: notifications.php");
    exit;
}

// Mark all notifications as read
if (isset($_POST['mark_all'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    header("Location: notifications.php");
    exit;
}

// Load all notifications for this user
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

$unreadTotal = count(array_filter($notifications, fn($n) => !$n['is_read']));

$pageTitle = 'Notifications';
require_once 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Notifications</h1>
                <p class="text-muted" style="margin: 0;">
                    <?= $unreadTotal ?> unread notification<?= $unreadTotal !== 1 ? 's' : '' ?>
                </p>
            </div>
            <?php if ($unreadTotal > 0): ?>
                <form method="POST">
                    <button type="submit" name="mark_all" class="btn btn-outline">Mark All as Read</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">You have no notifications yet.</div>
        <?php else: ?>
            <div style="background: var(--white); border-radius: 8px; box-shadow: var(--shadow); overflow: hidden;">
                <?php foreach ($notifications as $n): ?>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;
                                padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-light);
                                background: <?= $n['is_read'] ? 'transparent' : '#fffbeb' ?>;">
                        <div style="flex: 1;">
                            <p style="margin: 0 0 0.25rem; font-weight: <?= $n['is_read'] ? '400' : '600' ?>;">
                                <?= htmlspecialchars($n['message']) ?>
                            </p>
                            <small class="text-muted">
                                <?= date('M j, Y \a\t g:ia', strtotime($n['created_at'])) ?>
                            </small>
                        </div>
                        <?php if ($n['link']): ?>
                            <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-primary" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; white-space: nowrap; margin-left: 1rem;">
                                View
                            </a>
                        <?php endif; ?>
                        <?php if (!$n['is_read']): ?>
                            <a href="notifications.php?mark=<?= (int)$n['notification_id'] ?>"
                               class="btn btn-outline" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; white-space: nowrap; margin-left: 1rem;">
                                Mark as Read
                            </a>
                        <?php else: ?>
                            <span style="font-size: 0.8rem; color: var(--green); margin-left: 1rem; white-space: nowrap;">Read</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

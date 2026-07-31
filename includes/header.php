<?php
// Start session and check login before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $loginPath = (strpos($_SERVER['PHP_SELF'], '/auth/') !== false) ? 'login.php' : 'auth/login.php';
    header("Location: $loginPath");
    exit;
}

// Load unread count for the notification bell
$unreadCount = 0;
if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = (int) $stmt->fetchColumn();
}

$pageTitle = $pageTitle ?? 'CampusClaim';

// Sub-folder pages (items/, admin/, auth/) need '../' to reach root assets
$inSubfolder = (dirname($_SERVER['PHP_SELF']) !== '/');
$base = $inSubfolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | CampusClaim</title>
    <meta name="description" content="Centralized lost and found platform for Ashesi University students.">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3/src/index.js" defer></script>
</head>

<body>

    <header class="site-header">
        <div class="container">

            <a href="<?= htmlspecialchars($base) ?>index.php" class="logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="<?= htmlspecialchars($base) ?>assets/images/logo.png" alt="Ashesi Logo" style="height: 35px; border-radius: 4px;">
                Campus<span>Claim</span>
            </a>

            <ul class="nav-links">
                <li><a href="<?= $base ?>items/browse.php">Browse Items</a></li>
                <li><a href="<?= $base ?>items/report.php">Report Item</a></li>

                <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                    <li><a href="<?= $base ?>my_items.php">My Items</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="<?= $base ?>admin/dashboard.php">Admin Panel</a></li>
                <?php endif; ?>

                <!-- Notification bell -->
                <li class="nav-bell-wrap">
                    <button class="notif-bell" id="notif-bell" aria-label="Notifications">
                        <i class="ph ph-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                        <?php endif; ?>
                    </button>

                    <div id="notif-dropdown" class="notif-dropdown">
                        <div class="notif-header">
                            <strong>Notifications</strong>
                            <a href="<?= $base ?>notifications.php">View all</a>
                        </div>
                        <?php
                        if (isset($pdo)) {
                            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute([$_SESSION['user_id']]);
                            $recentNotifs = $stmt->fetchAll();
                        } else {
                            $recentNotifs = [];
                        }
                        ?>
                        <?php if (empty($recentNotifs)): ?>
                            <p class="notif-empty">No notifications yet</p>
                        <?php else: ?>
                            <?php foreach ($recentNotifs as $notif): ?>
                                <a href="<?= $base ?>notifications.php?mark=<?= $notif['notification_id'] ?>"
                                    class="notif-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                                    <span class="notif-msg"><?= htmlspecialchars($notif['message']) ?></span>
                                    <span class="notif-time text-muted">
                                        <?= date('M j, g:ia', strtotime($notif['created_at'])) ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </li>

                <li>
                    <a href="<?= $base ?>auth/logout.php" class="btn-nav btn">Log Out</a>
                </li>
            </ul>

        </div>
    </header>

    <div class="page-wrapper">
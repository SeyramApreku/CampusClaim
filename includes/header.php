<?php
// includes/header.php
// ─────────────────────────────────────────────
// Include this at the top of every protected page with:
//   require_once '../includes/header.php';  (from sub-folders)
//   require_once 'includes/header.php';     (from root)
//
// It starts the session, checks login, and renders the nav.
// Set $pageTitle before including this file for the <title> tag.
// ─────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Session guard — redirect to login if not logged in ──
if (!isset($_SESSION['user_id'])) {
    // Determine the correct path to login based on folder depth
    $loginPath = (strpos($_SERVER['PHP_SELF'], '/auth/') !== false)
        ? 'login.php'
        : 'auth/login.php';
    header("Location: $loginPath");
    exit;
}

// ── Fetch unread notification count for bell badge ──
$unreadCount = 0;
if (isset($pdo)) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = (int) $stmt->fetchColumn();
}

// ── Page title (fallback) ──
$pageTitle = $pageTitle ?? 'Ashesi Lost & Found';

// ── Determine base path for assets (root vs sub-folder) ──
// Pages in sub-folders (auth/, items/, admin/) need '../' prefix
$inSubfolder = (dirname($_SERVER['PHP_SELF']) !== '/');
$base = $inSubfolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Ashesi Lost &amp; Found</title>
    <meta name="description" content="Centralized lost and found platform for Ashesi University students.">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
    <!-- Phosphor Icons (lightweight icon set) -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3/src/index.js" defer></script>
</head>

<body>

    <!-- ══════════════════════════════════
     SITE HEADER / NAV
══════════════════════════════════ -->
    <header class="site-header">
        <div class="container">

            <!-- Logo -->
            <a href="<?= $base ?>index.php" class="logo">
                Ashesi <span>Lost&amp;Found</span>
            </a>

            <!-- Navigation links -->
            <ul class="nav-links">
                <li><a href="<?= $base ?>items/browse.php">Browse Items</a></li>
                <li><a href="<?= $base ?>items/report.php">Report Item</a></li>
                <li><a href="<?= $base ?>my_items.php">My Items</a></li>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="<?= $base ?>admin/dashboard.php">Admin</a></li>
                <?php endif; ?>

                <!-- Notification Bell -->
                <li class="nav-bell-wrap">
                    <button class="notif-bell" id="notif-bell" aria-label="Notifications">
                        <i class="ph ph-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                        <?php endif; ?>
                    </button>

                    <!-- Notification dropdown -->
                    <div id="notif-dropdown" class="notif-dropdown">
                        <div class="notif-header">
                            <strong>Notifications</strong>
                            <a href="<?= $base ?>notifications.php">View all</a>
                        </div>
                        <?php
                        // Show up to 5 recent notifications in dropdown
                        if (isset($pdo)) {
                            $stmt = $pdo->prepare(
                                "SELECT * FROM notifications WHERE user_id = ?
                             ORDER BY created_at DESC LIMIT 5"
                            );
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

                <!-- Logout -->
                <li>
                    <a href="<?= $base ?>auth/logout.php" class="btn-nav btn">
                        Log Out
                    </a>
                </li>
            </ul>

        </div>
    </header>

    <!-- Page content starts after header -->
    <div class="page-wrapper">
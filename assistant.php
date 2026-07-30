<?php

require_once 'classes/Database.php';
$pdo = Database::getInstance()->getConnection();

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

require_once 'classes/AIConfig.php';
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Item Assistant';
require_once 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div class="assistant-shell">
            <div class="assistant-heading">
                <h1>CampusClaim Assistant</h1>
                <p class="text-muted">Describe what you lost or found. Answers are grounded in currently open CampusClaim reports.</p>
            </div>

            <?php if (!AIConfig::assistantEnabled()): ?>
                <div class="alert alert-info">The assistant has not been enabled by an administrator yet.</div>
            <?php else: ?>
                <div id="assistant-messages" class="assistant-messages" aria-live="polite">
                    <div class="assistant-message assistant-message-bot">
                        Try: “Has anyone found a black phone near the Library this week?”
                    </div>
                </div>
                <form id="assistant-form" class="assistant-form">
                    <label for="assistant-question" class="sr-only">Ask about lost or found items</label>
                    <textarea id="assistant-question" maxlength="500" rows="3" required
                        placeholder="Describe the item, location, and approximate date..."></textarea>
                    <button type="submit" class="btn btn-primary">Ask Assistant</button>
                </form>
                <p class="assistant-disclaimer">Possible matches are suggestions only. Ownership is always verified through the normal claim process.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (AIConfig::assistantEnabled()): ?>
<script>
window.CAMPUSCLAIM_ASSISTANT = {
    endpoint: 'api/assistant.php',
    csrfToken: <?= json_encode($_SESSION['csrf_token']) ?>
};
</script>
<script src="assets/js/assistant.js?v=<?= filemtime(__DIR__ . '/assets/js/assistant.js') ?>"></script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

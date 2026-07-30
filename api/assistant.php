<?php

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../classes/AIConfig.php';
require_once __DIR__ . '/../classes/AssistantRateLimiter.php';
require_once __DIR__ . '/../classes/RAGAssistant.php';

function respond($status, array $payload)
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Method not allowed.']);
}
if (!isset($_SESSION['user_id'])) {
    respond(401, ['error' => 'Please log in to use the assistant.']);
}
if (!AIConfig::assistantEnabled() || !AIConfig::isConfigured()) {
    respond(503, ['error' => 'The assistant is not currently available.']);
}

$providedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if ($sessionToken === '' || !hash_equals($sessionToken, $providedToken)) {
    respond(403, ['error' => 'Invalid request token. Refresh the page and try again.']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$question = trim($input['question'] ?? '');
if ($question === '' || strlen($question) > 2000) {
    respond(422, ['error' => 'Enter a question of 500 characters or fewer.']);
}
if (!(new AssistantRateLimiter())->allow((int) $_SESSION['user_id'])) {
    respond(429, ['error' => 'Too many questions. Please wait a minute and try again.']);
}

try {
    respond(200, (new RAGAssistant())->ask($question));
} catch (Throwable $e) {
    error_log('RAG assistant failure: ' . $e->getMessage());
    respond(503, ['error' => 'The assistant could not answer right now. Please try again shortly.']);
}

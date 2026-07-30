<?php

require_once __DIR__ . '/../classes/AIConfig.php';
require_once __DIR__ . '/../classes/ItemIndexer.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (!AIConfig::isConfigured()) {
    fwrite(STDERR, "AI indexing is not configured. Check AI_FEATURES_ENABLED, OPENAI_API_KEY, and QDRANT_URL.\n");
    exit(1);
}

$limit = max(1, (int) ($argv[1] ?? 50));
$indexer = new ItemIndexer();
$vectors = new QdrantVectorStore();
$vectors->ensureCollection();

$processed = 0;
while ($processed < $limit && $indexer->processNext()) {
    $processed++;
}

fwrite(STDOUT, "Processed $processed indexing job(s).\n");

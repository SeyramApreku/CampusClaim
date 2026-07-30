<?php

require_once __DIR__ . '/../classes/AIConfig.php';
require_once __DIR__ . '/../classes/OpenAIEmbeddingProvider.php';
require_once __DIR__ . '/../classes/QdrantVectorStore.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (!AIConfig::isConfigured()) {
    fwrite(STDERR, "Missing AI configuration. Check OPENAI_API_KEY and QDRANT_URL.\n");
    exit(1);
}

try {
    $embedding = (new OpenAIEmbeddingProvider())->embed('CampusClaim connection test');
    if (count($embedding) !== AIConfig::vectorSize()) {
        throw new RuntimeException(
            'Unexpected embedding size: ' . count($embedding)
        );
    }
    fwrite(STDOUT, "OpenAI connection: OK\n");

    (new QdrantVectorStore())->ensureCollection();
    fwrite(STDOUT, "Qdrant connection: OK\n");
    fwrite(STDOUT, "AI services are ready.\n");
} catch (Throwable $e) {
    fwrite(STDERR, "Connection check failed: " . $e->getMessage() . "\n");
    exit(1);
}

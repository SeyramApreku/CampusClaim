<?php

require_once __DIR__ . '/AIConfig.php';
require_once __DIR__ . '/HttpClient.php';
require_once __DIR__ . '/EmbeddingProvider.php';

class OpenAIEmbeddingProvider implements EmbeddingProvider
{
    private $http;

    public function __construct(HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
    }

    public function embed($text)
    {
        if (AIConfig::openAIKey() === '') {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $result = $this->http->request(
            'POST',
            'https://api.openai.com/v1/embeddings',
            ['Authorization: Bearer ' . AIConfig::openAIKey()],
            [
                'model' => AIConfig::embeddingModel(),
                'input' => $text,
                'dimensions' => AIConfig::vectorSize(),
                'encoding_format' => 'float',
            ]
        );

        if (!isset($result['data'][0]['embedding'])) {
            throw new RuntimeException('Embedding response did not contain a vector.');
        }

        return $result['data'][0]['embedding'];
    }
}

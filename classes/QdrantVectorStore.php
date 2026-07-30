<?php

require_once __DIR__ . '/AIConfig.php';
require_once __DIR__ . '/HttpClient.php';
require_once __DIR__ . '/VectorStore.php';

class QdrantVectorStore implements VectorStore
{
    private $http;

    public function __construct(HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
    }

    private function headers()
    {
        return AIConfig::qdrantKey() === '' ? [] : ['api-key: ' . AIConfig::qdrantKey()];
    }

    private function collectionUrl($suffix = '')
    {
        if (AIConfig::qdrantUrl() === '') {
            throw new RuntimeException('QDRANT_URL is not configured.');
        }
        return AIConfig::qdrantUrl() . '/collections/' . rawurlencode(AIConfig::collection()) . $suffix;
    }

    public function ensureCollection()
    {
        try {
            $this->http->request('GET', $this->collectionUrl(), $this->headers());
        } catch (RuntimeException $e) {
            $this->http->request('PUT', $this->collectionUrl(), $this->headers(), [
                'vectors' => ['size' => AIConfig::vectorSize(), 'distance' => 'Cosine'],
            ]);
        }

        foreach (['type', 'status'] as $field) {
            $this->http->request(
                'PUT',
                $this->collectionUrl('/index?wait=true'),
                $this->headers(),
                [
                    'field_name' => $field,
                    'field_schema' => 'keyword',
                ]
            );
        }
    }

    public function upsert($itemId, array $vector, array $payload)
    {
        $this->http->request('PUT', $this->collectionUrl('/points?wait=true'), $this->headers(), [
            'points' => [[
                'id' => (int) $itemId,
                'vector' => $vector,
                'payload' => $payload,
            ]],
        ]);
    }

    public function delete($itemId)
    {
        $this->http->request('POST', $this->collectionUrl('/points/delete?wait=true'), $this->headers(), [
            'points' => [(int) $itemId],
        ]);
    }

    public function search(array $vector, array $filters = [], $limit = 20)
    {
        $must = [];
        foreach ($filters as $key => $value) {
            $must[] = ['key' => $key, 'match' => ['value' => $value]];
        }

        $body = [
            'query' => $vector,
            'limit' => (int) $limit,
            'with_payload' => true,
            'score_threshold' => 0.25,
        ];
        if ($must) {
            $body['filter'] = ['must' => $must];
        }

        $response = $this->http->request('POST', $this->collectionUrl('/points/query'), $this->headers(), $body);
        return $response['result']['points'] ?? $response['result'] ?? [];
    }
}

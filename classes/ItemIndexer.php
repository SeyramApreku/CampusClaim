<?php

require_once __DIR__ . '/AIConfig.php';
require_once __DIR__ . '/IndexJobDAO.php';
require_once __DIR__ . '/ItemDAO.php';
require_once __DIR__ . '/ItemDocumentBuilder.php';
require_once __DIR__ . '/OpenAIEmbeddingProvider.php';
require_once __DIR__ . '/QdrantVectorStore.php';

class ItemIndexer
{
    private $jobs;
    private $items;
    private $builder;
    private $embeddings;
    private $vectors;

    public function __construct(
        IndexJobDAO $jobs = null,
        ItemDAO $items = null,
        ItemDocumentBuilder $builder = null,
        EmbeddingProvider $embeddings = null,
        VectorStore $vectors = null
    ) {
        $this->jobs = $jobs ?? new IndexJobDAO();
        $this->items = $items ?? new ItemDAO();
        $this->builder = $builder ?? new ItemDocumentBuilder();
        $this->embeddings = $embeddings ?? new OpenAIEmbeddingProvider();
        $this->vectors = $vectors ?? new QdrantVectorStore();
    }

    public function processNext()
    {
        $job = $this->jobs->claimNext();
        if (!$job) {
            return false;
        }

        try {
            if ($job['operation'] === 'delete') {
                $this->vectors->delete($job['item_id']);
            } else {
                $item = $this->items->getItemById($job['item_id']);
                if (!$item || $item['status'] !== 'open') {
                    $this->vectors->delete($job['item_id']);
                } else {
                    $document = $this->builder->build($item);
                    $checksum = $this->builder->checksum($document);
                    if ($checksum !== $this->jobs->checksumFor($job['item_id'])) {
                        $vector = $this->embeddings->embed($document);
                        $this->vectors->upsert($job['item_id'], $vector, [
                            'item_id' => (int) $job['item_id'],
                            'type' => $item['type'],
                            'status' => $item['status'],
                            'category_id' => (int) $item['category_id'],
                            'location_id' => (int) $item['location_id'],
                            'item_date' => $item['item_date'],
                            'document_version' => 1,
                            'content_checksum' => $checksum,
                        ]);
                        $this->jobs->saveChecksum(
                            $job['item_id'],
                            $checksum,
                            AIConfig::embeddingModel()
                        );
                    }
                }
            }
            $this->jobs->complete($job['job_id']);
        } catch (Throwable $e) {
            $this->jobs->fail($job['job_id'], $e->getMessage(), $job['attempts'] + 1);
            throw $e;
        }

        return true;
    }
}

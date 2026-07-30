<?php

require_once __DIR__ . '/AIConfig.php';
require_once __DIR__ . '/MatchingStrategy.php';
require_once __DIR__ . '/ItemDocumentBuilder.php';
require_once __DIR__ . '/OpenAIEmbeddingProvider.php';
require_once __DIR__ . '/QdrantVectorStore.php';

class SemanticMatchingStrategy implements MatchingStrategy
{
    private $embeddings;
    private $vectors;
    private $builder;
    private $fallback;

    public function __construct(
        EmbeddingProvider $embeddings = null,
        VectorStore $vectors = null,
        ItemDocumentBuilder $builder = null,
        MatchingStrategy $fallback = null
    ) {
        $this->embeddings = $embeddings ?? new OpenAIEmbeddingProvider();
        $this->vectors = $vectors ?? new QdrantVectorStore();
        $this->builder = $builder ?? new ItemDocumentBuilder();
        $this->fallback = $fallback ?? new FlexibleMatchingStrategy();
    }

    public function findMatches(Item $item, ItemDAO $dao)
    {
        if (!AIConfig::isConfigured()) {
            return $this->fallback->findMatches($item, $dao);
        }

        try {
            $source = $dao->getItemById($item->getId());
            if (!$source) return [];

            $query = $this->embeddings->embed($this->builder->build($source));
            $targetType = $item->getType() === 'lost' ? 'found' : 'lost';
            $hits = $this->vectors->search($query, [
                'type' => $targetType,
                'status' => 'open',
            ], 30);

            $semanticById = [];
            foreach ($hits as $hit) {
                $id = (int) ($hit['id'] ?? $hit['payload']['item_id'] ?? 0);
                if ($id && $id !== (int) $item->getId()) {
                    $semanticById[$id] = (float) ($hit['score'] ?? 0);
                }
            }

            $candidates = $dao->getItemsByIds(array_keys($semanticById));
            $ranked = [];
            foreach ($semanticById as $candidateId => $semantic) {
                if (!isset($candidates[$candidateId])) continue;
                $candidate = $candidates[$candidateId];
                if ($candidate['status'] !== 'open' || $candidate['type'] !== $targetType) continue;

                $category = (int) $candidate['category_id'] === (int) $item->getCategoryId() ? 1.0 : 0.0;
                $location = (int) $candidate['location_id'] === (int) $item->getLocationId() ? 1.0 : 0.0;
                $days = abs((strtotime($candidate['item_date']) - strtotime($item->getItemDate())) / 86400);
                $date = max(0, 1 - ($days / 30));
                $lexical = $this->lexicalOverlap(
                    $item->getTitle() . ' ' . $item->getDescription(),
                    $candidate['title'] . ' ' . $candidate['description']
                );

                $combined = (0.60 * $semantic)
                    + (0.15 * $category)
                    + (0.10 * $location)
                    + (0.10 * $date)
                    + (0.05 * $lexical);

                if ($semantic < 0.30 || $combined < 0.48) continue;

                $reasons = ['similar description'];
                if ($category) $reasons[] = 'same category';
                if ($location) $reasons[] = 'same location';
                if ($days <= 3) $reasons[] = 'reported within 3 days';

                $candidate['semantic_score'] = round($semantic, 4);
                $candidate['match_score'] = round($combined, 4);
                $candidate['match_explanation'] = ucfirst(implode(', ', $reasons)) . '.';
                $ranked[] = $candidate;
            }

            usort($ranked, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
            return array_slice($ranked, 0, 10);
        } catch (Throwable $e) {
            error_log('Semantic matching fallback: ' . $e->getMessage());
            return $this->fallback->findMatches($item, $dao);
        }
    }

    private function lexicalOverlap($left, $right)
    {
        $tokenize = function ($text) {
            $words = preg_split('/[^a-z0-9]+/i', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
            return array_values(array_unique(array_filter($words, fn($word) => strlen($word) > 2)));
        };
        $a = $tokenize($left);
        $b = $tokenize($right);
        if (!$a || !$b) return 0.0;
        return count(array_intersect($a, $b)) / count(array_unique(array_merge($a, $b)));
    }
}

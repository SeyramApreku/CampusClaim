<?php

require_once __DIR__ . '/Database.php';

class MatchScoreDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function record($sourceId, $candidateId, $semantic, $combined, $explanation)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO item_match_scores
                (source_item_id, candidate_item_id, semantic_score, combined_score, explanation)
             VALUES (:source, :candidate, :semantic, :combined, :explanation)
             ON DUPLICATE KEY UPDATE semantic_score = VALUES(semantic_score),
                 combined_score = VALUES(combined_score), explanation = VALUES(explanation)"
        );
        $stmt->execute([
            ':source' => $sourceId,
            ':candidate' => $candidateId,
            ':semantic' => $semantic,
            ':combined' => $combined,
            ':explanation' => $explanation,
        ]);
    }

    public function markNotifiedIfNew($sourceId, $candidateId)
    {
        $stmt = $this->db->prepare(
            "UPDATE item_match_scores SET notified_at = CURRENT_TIMESTAMP
             WHERE source_item_id = :source AND candidate_item_id = :candidate
               AND notified_at IS NULL"
        );
        $stmt->execute([':source' => $sourceId, ':candidate' => $candidateId]);
        return $stmt->rowCount() === 1;
    }
}

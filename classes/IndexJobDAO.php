<?php

require_once __DIR__ . '/Database.php';

class IndexJobDAO
{
    private $db;

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    public function enqueue($itemId, $operation = 'upsert')
    {
        $stmt = $this->db->prepare(
            "INSERT INTO item_index_jobs (item_id, operation, status)
             VALUES (:item_id, :operation, 'pending')"
        );
        return $stmt->execute([':item_id' => $itemId, ':operation' => $operation]);
    }

    public function enqueueAllItems()
    {
        return $this->db->exec(
            "INSERT INTO item_index_jobs (item_id, operation, status)
             SELECT item_id, 'upsert', 'pending' FROM items"
        );
    }

    public function claimNext()
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                "SELECT * FROM item_index_jobs
                 WHERE status IN ('pending', 'failed')
                   AND available_at <= CURRENT_TIMESTAMP
                   AND attempts < 5
                 ORDER BY job_id ASC
                 LIMIT 1
                 FOR UPDATE"
            );
            $job = $stmt->fetch();
            if (!$job) {
                $this->db->commit();
                return null;
            }

            $update = $this->db->prepare(
                "UPDATE item_index_jobs
                 SET status = 'processing', attempts = attempts + 1, last_error = NULL
                 WHERE job_id = :job_id"
            );
            $update->execute([':job_id' => $job['job_id']]);
            $this->db->commit();
            return $job;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function complete($jobId)
    {
        $stmt = $this->db->prepare(
            "UPDATE item_index_jobs SET status = 'completed' WHERE job_id = :job_id"
        );
        return $stmt->execute([':job_id' => $jobId]);
    }

    public function fail($jobId, $error, $attempts)
    {
        $delayMinutes = min(60, 2 ** max(0, (int) $attempts));
        $stmt = $this->db->prepare(
            "UPDATE item_index_jobs
             SET status = 'failed',
                 last_error = :error,
                 available_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL $delayMinutes MINUTE)
             WHERE job_id = :job_id"
        );
        return $stmt->execute([
            ':job_id' => $jobId,
            ':error' => substr($error, 0, 2000),
        ]);
    }

    public function checksumFor($itemId)
    {
        $stmt = $this->db->prepare(
            "SELECT content_checksum FROM item_index_state WHERE item_id = :item_id"
        );
        $stmt->execute([':item_id' => $itemId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function saveChecksum($itemId, $checksum, $model)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO item_index_state (item_id, content_checksum, embedding_model, indexed_at)
             VALUES (:item_id, :checksum, :model, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE content_checksum = VALUES(content_checksum),
                 embedding_model = VALUES(embedding_model), indexed_at = CURRENT_TIMESTAMP"
        );
        return $stmt->execute([
            ':item_id' => $itemId,
            ':checksum' => $checksum,
            ':model' => $model,
        ]);
    }
}

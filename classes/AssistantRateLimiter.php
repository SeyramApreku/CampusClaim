<?php

require_once __DIR__ . '/Database.php';

class AssistantRateLimiter
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function allow($userId, $limit = 12)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO assistant_rate_limits (user_id, window_started_at, request_count)
             VALUES (:user_id, CURRENT_TIMESTAMP, 1)
             ON DUPLICATE KEY UPDATE
                request_count = IF(
                    window_started_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MINUTE),
                    1,
                    request_count + 1
                ),
                window_started_at = IF(
                    window_started_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MINUTE),
                    CURRENT_TIMESTAMP,
                    window_started_at
                )"
        );
        $stmt->execute([':user_id' => $userId]);

        $check = $this->db->prepare(
            "SELECT request_count FROM assistant_rate_limits WHERE user_id = :user_id"
        );
        $check->execute([':user_id' => $userId]);
        return (int) $check->fetchColumn() <= $limit;
    }
}

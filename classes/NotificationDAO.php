<?php
// classes/NotificationDAO.php
require_once 'Database.php';

class NotificationDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new notification for a specific user.
     * @param int $user_id The recipient
     * @param string $message The notification text
     * @param string|null $link Optional relative URL for the 'View' button
     */
    public function createNotification($user_id, $message, $link = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message, link) 
             VALUES (:user_id, :message, :link)"
        );
        return $stmt->execute([
            ':user_id' => $user_id,
            ':message' => $message,
            ':link' => $link
        ]);
    }
}
?>

<?php
// classes/ClaimDAO.php
// Implements the DAO Pattern for Claims

require_once 'Database.php';
require_once 'Claim.php';

class ClaimDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Submit a new claim
    public function createClaim(Claim $claim) {
        $stmt = $this->db->prepare(
            "INSERT INTO claims (item_id, user_id, proof_description, status)
             VALUES (:item_id, :user_id, :proof_description, 'pending')"
        );

        return $stmt->execute([
            ':item_id' => $claim->getItemId(),
            ':user_id' => $claim->getUserId(),
            ':proof_description' => $claim->getProofDescription()
        ]);
    }

    // Check if a user has already claimed this item
    public function hasUserClaimedItem($user_id, $item_id) {
        $stmt = $this->db->prepare("SELECT claim_id FROM claims WHERE user_id = :user_id AND item_id = :item_id LIMIT 1");
        $stmt->execute([
            ':user_id' => $user_id,
            ':item_id' => $item_id
        ]);
        return $stmt->fetch() !== false;
    }

    // --- Admin Methods ---

    // Get all pending claims joined with user and item data
    public function getAllPendingClaims() {
        $sql = "SELECT c.*, i.title as item_title, u.name as claimant_name, u.email as claimant_email
                FROM claims c
                JOIN items i ON c.item_id = i.item_id
                JOIN users u ON c.user_id = u.user_id
                WHERE c.status = 'pending'
                ORDER BY c.created_at ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Approve a claim
    public function approveClaim($claim_id) {
        $stmt = $this->db->prepare("UPDATE claims SET status = 'approved' WHERE claim_id = :id");
        return $stmt->execute([':id' => $claim_id]);
    }

    // Reject a claim
    public function rejectClaim($claim_id) {
        $stmt = $this->db->prepare("UPDATE claims SET status = 'rejected' WHERE claim_id = :id");
        return $stmt->execute([':id' => $claim_id]);
    }
}
?>

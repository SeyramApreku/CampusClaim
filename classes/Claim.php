<?php
// classes/Claim.php

class Claim {
    private $claim_id;
    private $item_id;
    private $user_id;
    private $proof_description;
    private $status;
    private $created_at;

    public function __construct($data) {
        $this->claim_id = $data['claim_id'] ?? null;
        $this->item_id = $data['item_id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->proof_description = $data['proof_description'] ?? '';
        $this->status = $data['status'] ?? 'pending';
        $this->created_at = $data['created_at'] ?? '';
    }

    public function getClaimId() { return $this->claim_id; }
    public function getItemId() { return $this->item_id; }
    public function getUserId() { return $this->user_id; }
    public function getProofDescription() { return $this->proof_description; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
}
?>

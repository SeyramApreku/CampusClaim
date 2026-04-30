<?php
// classes/Item.php

abstract class Item {
    protected $item_id;
    protected $user_id;
    protected $type;
    protected $title;
    protected $description;
    protected $category_id;
    protected $location_id;
    protected $item_date;
    protected $item_time;
    protected $status;
    protected $image_url;
    protected $created_at;

    public function __construct($data) {
        $this->item_id = $data['item_id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->type = $data['type'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->category_id = $data['category_id'] ?? null;
        $this->location_id = $data['location_id'] ?? null;
        $this->item_date = $data['item_date'] ?? '';
        $this->item_time = $data['item_time'] ?? '';
        $this->status = $data['status'] ?? 'open';
        $this->image_url = $data['image_url'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
    }

    // Getters
    public function getId() { return $this->item_id; }
    public function getUserId() { return $this->user_id; }
    public function getType() { return $this->type; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getCategoryId() { return $this->category_id; }
    public function getLocationId() { return $this->location_id; }
    public function getItemDate() { return $this->item_date; }
    public function getItemTime() { return $this->item_time; }
    public function getStatus() { return $this->status; }
    public function getImageUrl() { return $this->image_url; }
    public function getCreatedAt() { return $this->created_at; }

    // Abstract method that children must implement
    abstract public function getDisplayBadge();
}

class LostItem extends Item {
    public function getDisplayBadge() {
        return '<span class="badge badge-danger">Lost</span>';
    }
}

class FoundItem extends Item {
    public function getDisplayBadge() {
        return '<span class="badge badge-success">Found</span>';
    }
}
?>

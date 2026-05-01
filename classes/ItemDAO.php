<?php
// classes/ItemDAO.php
// Implements the Data Access Object (DAO) Design Pattern for Items

require_once 'Database.php';
require_once 'ItemFactory.php';

class ItemDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all categories for dropdowns
    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY category_name ASC");
        return $stmt->fetchAll();
    }

    // Get all locations for dropdowns
    public function getLocations() {
        $stmt = $this->db->query("SELECT * FROM locations ORDER BY location_name ASC");
        return $stmt->fetchAll();
    }

    // Create a new item (Lost or Found) and return the new item_id
    public function createItem(Item $item) {
        $stmt = $this->db->prepare(
            "INSERT INTO items (user_id, type, title, description, category_id, location_id, item_date, item_time, image_url, status)
             VALUES (:user_id, :type, :title, :description, :category_id, :location_id, :item_date, :item_time, :image_url, 'open')"
        );

        $success = $stmt->execute([
            ':user_id' => $item->getUserId(),
            ':type' => $item->getType(),
            ':title' => $item->getTitle(),
            ':description' => $item->getDescription(),
            ':category_id' => $item->getCategoryId(),
            ':location_id' => $item->getLocationId(),
            ':item_date' => $item->getItemDate(),
            ':item_time' => $item->getItemTime() ?: null,
            ':image_url' => $item->getImageUrl() ?: null
        ]);

        if ($success) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Fetch all open items with category and location names, with optional search query and filters
    public function getAllOpenItems($search_query = '', $filters = []) {
        $sql = "SELECT i.*, c.category_name, l.location_name, u.name as reporter_name 
                FROM items i
                JOIN categories c ON i.category_id = c.category_id
                JOIN locations l ON i.location_id = l.location_id
                JOIN users u ON i.user_id = u.user_id
                WHERE i.status = 'open'";
                
        $params = [];
        
        if (!empty($search_query)) {
            $sql .= " AND (i.title LIKE :query OR i.description LIKE :query)";
            $params[':query'] = "%" . $search_query . "%";
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND i.type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND i.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['location_id'])) {
            $sql .= " AND i.location_id = :location_id";
            $params[':location_id'] = $filters['location_id'];
        }
        
        $sortMap = [
            'oldest'   => 'i.created_at ASC',
            'title_az' => 'i.title ASC',
            'title_za' => 'i.title DESC',
            'newest'   => 'i.created_at DESC',
        ];
        $orderBy = $sortMap[$filters['sort'] ?? 'newest'] ?? 'i.created_at DESC';
        $sql .= " ORDER BY $orderBy";

        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get specific item details by ID
    public function getItemById($item_id) {
        $sql = "SELECT i.*, c.category_name, l.location_name, u.name as reporter_name 
                FROM items i
                JOIN categories c ON i.category_id = c.category_id
                JOIN locations l ON i.location_id = l.location_id
                JOIN users u ON i.user_id = u.user_id
                WHERE i.item_id = :item_id LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':item_id' => $item_id]);
        return $stmt->fetch();
    }

    // Auto-Matching Algorithm Query
    public function findMatchesByLocationAndCategory($targetType, $category_id, $location_id) {
        $sql = "SELECT i.*, c.category_name, l.location_name, u.name as reporter_name 
                FROM items i
                JOIN categories c ON i.category_id = c.category_id
                JOIN locations l ON i.location_id = l.location_id
                JOIN users u ON i.user_id = u.user_id
                WHERE i.status = 'open' 
                AND i.type = :type 
                AND i.category_id = :cat_id 
                AND i.location_id = :loc_id
                ORDER BY i.created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':type' => $targetType,
            ':cat_id' => $category_id,
            ':loc_id' => $location_id
        ]);
        return $stmt->fetchAll();
    }

    // Dashboard statistics and user activity

    public function getActiveReportsCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM items WHERE user_id = :id AND status = 'open'");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchColumn();
    }

    public function getRecoveredItemsCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM items WHERE user_id = :id AND status = 'claimed'");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchColumn();
    }

    public function getRecentActivity($userId) {
        $stmt = $this->db->prepare("
            SELECT item_id, title, type, status, created_at 
            FROM items 
            WHERE user_id = :id 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getItemsByUserId($userId) {
        $sql = "SELECT i.*, c.category_name, l.location_name 
                FROM items i
                JOIN categories c ON i.category_id = c.category_id
                JOIN locations l ON i.location_id = l.location_id
                WHERE i.user_id = :user_id
                ORDER BY i.created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function deleteItem($item_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM items WHERE item_id = :id AND user_id = :uid");
        return $stmt->execute([
            ':id' => $item_id,
            ':uid' => $user_id
        ]);
    }

    // Admin and workflow helper methods

    // Update the status of an item (e.g. from 'open' to 'claimed' or 'resolved')
    public function updateItemStatus($item_id, $status) {
        $stmt = $this->db->prepare("UPDATE items SET status = :status WHERE item_id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $item_id
        ]);
    }
}
?>

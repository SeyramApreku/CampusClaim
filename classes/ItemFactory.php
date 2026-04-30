<?php
// classes/ItemFactory.php
// Implements the Factory Method Design Pattern for Items

require_once 'Item.php';

class ItemFactory {
    public static function createItem($data) {
        if (!isset($data['type'])) {
            throw new Exception("Item type not specified.");
        }

        if ($data['type'] === 'lost') {
            return new LostItem($data);
        } elseif ($data['type'] === 'found') {
            return new FoundItem($data);
        }

        throw new Exception("Invalid item type.");
    }
}
?>

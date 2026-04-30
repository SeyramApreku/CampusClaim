<?php
// classes/MatchingStrategy.php
// Implements the Strategy Design Pattern for Auto-Matching

require_once 'ItemDAO.php';
require_once 'Item.php';

interface MatchingStrategy {
    /**
     * @param Item $item The item just reported
     * @param ItemDAO $dao The data access object to query the DB
     * @return array List of potential matches
     */
    public function findMatches(Item $item, ItemDAO $dao);
}

class ExactLocationCategoryStrategy implements MatchingStrategy {
    public function findMatches(Item $item, ItemDAO $dao) {
        // If they lost an item, find 'found' items.
        // If they found an item, find 'lost' items.
        $targetType = ($item->getType() === 'lost') ? 'found' : 'lost';

        // Call DAO to fetch matches
        return $dao->findMatchesByLocationAndCategory(
            $targetType, 
            $item->getCategoryId(), 
            $item->getLocationId()
        );
    }
}
?>

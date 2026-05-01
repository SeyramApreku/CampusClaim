<?php
// classes/MatchingStrategy.php

require_once 'ItemDAO.php';
require_once 'Item.php';

interface MatchingStrategy {
    public function findMatches(Item $item, ItemDAO $dao);
}

/**
 * Matches items based on exact Category and Location.
 */
class ExactLocationCategoryStrategy implements MatchingStrategy {
    public function findMatches(Item $item, ItemDAO $dao) {
        $targetType = ($item->getType() === 'lost') ? 'found' : 'lost';
        return $dao->findMatchesByLocationAndCategory(
            $targetType, 
            $item->getCategoryId(), 
            $item->getLocationId()
        );
    }
}

/**
 * Matches items based on Category + Title keywords or Location.
 */
class FlexibleMatchingStrategy implements MatchingStrategy {
    public function findMatches(Item $item, ItemDAO $dao) {
        $targetType = ($item->getType() === 'lost') ? 'found' : 'lost';
        
        // 1. Start with Category + Location exact match (highest priority)
        $exact = $dao->findMatchesByLocationAndCategory(
            $targetType, 
            $item->getCategoryId(), 
            $item->getLocationId()
        );

        // 2. Add Category + Title keyword matches
        $keywords = explode(' ', $item->getTitle());
        // Clean up keywords
        $keywords = array_filter($keywords, function($k) {
            return strlen($k) > 2; // ignore small words
        });

        $keywordMatches = [];
        if (!empty($keywords)) {
            $keywordMatches = $dao->findMatchesByKeywords(
                $targetType,
                $item->getCategoryId(),
                $keywords
            );
        }

        // Combine and unique by item_id
        $allMatches = array_merge($exact, $keywordMatches);
        $uniqueMatches = [];
        $ids = [];
        foreach ($allMatches as $m) {
            if (!in_array($m['item_id'], $ids) && $m['item_id'] != $item->getId()) {
                $ids[] = $m['item_id'];
                $uniqueMatches[] = $m;
            }
        }

        return $uniqueMatches;
    }
}
?>

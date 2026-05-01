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
 * Smart Matching Strategy:
 * 1. Must be the same Category.
 * 2. Must have at least one keyword match in the title.
 * 3. Location match provides a higher 'confidence' but is no longer the sole criteria.
 */
class FlexibleMatchingStrategy implements MatchingStrategy {
    public function findMatches(Item $item, ItemDAO $dao) {
        $targetType = ($item->getType() === 'lost') ? 'found' : 'lost';
        
        // Extract keywords from the reported title (ignore common small words)
        $keywords = explode(' ', strtolower($item->getTitle()));
        $keywords = array_filter($keywords, function($k) {
            return strlen($k) > 2; 
        });

        if (empty($keywords)) {
            // Fallback to Location+Category if title is too short to extract keywords
            return $dao->findMatchesByLocationAndCategory(
                $targetType, 
                $item->getCategoryId(), 
                $item->getLocationId()
            );
        }

        // Fetch candidates that share the same Category and at least one keyword
        $candidates = $dao->findMatchesByKeywords(
            $targetType,
            $item->getCategoryId(),
            $keywords
        );

        // Optional: We can further filter or sort these candidates.
        // For example, prioritize those that ALSO match the location.
        usort($candidates, function($a, $b) use ($item) {
            $aLocMatch = ($a['location_id'] == $item->getLocationId());
            $bLocMatch = ($b['location_id'] == $item->getLocationId());
            
            if ($aLocMatch && !$bLocMatch) return -1;
            if (!$aLocMatch && $bLocMatch) return 1;
            return 0;
        });

        return $candidates;
    }
}
?>

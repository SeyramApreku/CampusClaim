<?php
// classes/MatchingManager.php

require_once 'ItemDAO.php';
require_once 'MatchingStrategy.php';
require_once 'NotificationDAO.php';
require_once 'AIConfig.php';
require_once 'SemanticMatchingStrategy.php';
require_once 'MatchScoreDAO.php';

class MatchingManager {
    private $itemDAO;
    private $notifDAO;
    private $strategy;

    public function __construct(ItemDAO $itemDAO, NotificationDAO $notifDAO, MatchingStrategy $strategy = null) {
        $this->itemDAO = $itemDAO;
        $this->notifDAO = $notifDAO;
        // Default to Flexible matching if none provided
        $this->strategy = $strategy ?? (
            AIConfig::enabled() ? new SemanticMatchingStrategy() : new FlexibleMatchingStrategy()
        );
    }

    /**
     * Run matching for a newly reported item and notify existing owners.
     * @param Item $newItem The newly reported item object.
     * @return array List of matches found.
     */
    public function runMatching(Item $newItem) {
        // 1. Find potential matches in the system
        $matches = $this->strategy->findMatches($newItem, $this->itemDAO);

        if (empty($matches)) {
            return [];
        }

        // 2. Notify the owners of those matches
        // For each existing item that matches the new report, tell them a match was found.
        foreach ($matches as $matchData) {
            $ownerId = $matchData['user_id'];
            $matchTitle = $matchData['title'];
            $newTitle = $newItem->getTitle();
            
            if (isset($matchData['match_score'])) {
                $scores = new MatchScoreDAO();
                $scores->record(
                    $newItem->getId(),
                    $matchData['item_id'],
                    $matchData['semantic_score'],
                    $matchData['match_score'],
                    $matchData['match_explanation']
                );
                if ($matchData['match_score'] < 0.68
                    || !$scores->markNotifiedIfNew($newItem->getId(), $matchData['item_id'])) {
                    continue;
                }
            }

            $msg = "A new " . $newItem->getType() . " item '" . $newTitle . "' was just reported that might match your " . $matchData['type'] . " item '" . $matchTitle . "'.";
            
            // Link to the details of the new item for the existing owner to check
            $this->notifDAO->createNotification($ownerId, $msg, "items/details.php?id=" . $newItem->getId());
        }

        return $matches;
    }
}
?>

<?php

class ItemDocumentBuilder
{
    public function build(array $item)
    {
        return implode("\n", [
            'Report type: ' . ($item['type'] ?? ''),
            'Title: ' . ($item['title'] ?? ''),
            'Description: ' . ($item['description'] ?? ''),
            'Category: ' . ($item['category_name'] ?? ''),
            'Location: ' . ($item['location_name'] ?? ''),
            'Date: ' . ($item['item_date'] ?? ''),
        ]);
    }

    public function checksum($document)
    {
        return hash('sha256', $document);
    }
}

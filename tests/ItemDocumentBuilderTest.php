<?php

require_once __DIR__ . '/../classes/ItemDocumentBuilder.php';

$builder = new ItemDocumentBuilder();
$item = [
    'type' => 'lost',
    'title' => 'Black phone',
    'description' => 'Cracked rear glass',
    'category_name' => 'Electronics',
    'location_name' => 'Library',
    'item_date' => '2026-07-30',
];

$document = $builder->build($item);
$expectedParts = [
    'Report type: lost',
    'Title: Black phone',
    'Description: Cracked rear glass',
    'Category: Electronics',
    'Location: Library',
    'Date: 2026-07-30',
];

foreach ($expectedParts as $part) {
    if (strpos($document, $part) === false) {
        fwrite(STDERR, "Missing document field: $part\n");
        exit(1);
    }
}

if ($builder->checksum($document) !== $builder->checksum($document)) {
    fwrite(STDERR, "Document checksum is not deterministic.\n");
    exit(1);
}

fwrite(STDOUT, "ItemDocumentBuilderTest passed.\n");

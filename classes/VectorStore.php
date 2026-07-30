<?php

interface VectorStore
{
    public function ensureCollection();
    public function upsert($itemId, array $vector, array $payload);
    public function delete($itemId);
    public function search(array $vector, array $filters = [], $limit = 20);
}

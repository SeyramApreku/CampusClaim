<?php

class AIConfig
{
    public static function enabled()
    {
        return filter_var(getenv('AI_FEATURES_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN);
    }

    public static function assistantEnabled()
    {
        return self::enabled()
            && filter_var(getenv('RAG_ASSISTANT_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN);
    }

    public static function openAIKey()
    {
        return getenv('OPENAI_API_KEY') ?: '';
    }

    public static function embeddingModel()
    {
        return getenv('OPENAI_EMBEDDING_MODEL') ?: 'text-embedding-3-small';
    }

    public static function chatModel()
    {
        return getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4.1-mini';
    }

    public static function qdrantUrl()
    {
        return rtrim(getenv('QDRANT_URL') ?: '', '/');
    }

    public static function qdrantKey()
    {
        return getenv('QDRANT_API_KEY') ?: '';
    }

    public static function collection()
    {
        return getenv('QDRANT_COLLECTION') ?: 'campusclaim_items';
    }

    public static function vectorSize()
    {
        return (int) (getenv('EMBEDDING_DIMENSIONS') ?: 1536);
    }

    public static function isConfigured()
    {
        return self::enabled()
            && self::openAIKey() !== ''
            && self::qdrantUrl() !== '';
    }
}

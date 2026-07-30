<?php

require_once __DIR__ . '/AIConfig.php';
require_once __DIR__ . '/HttpClient.php';

class OpenAIResponseProvider
{
    private $http;

    public function __construct(HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
    }

    public function answer($instructions, $input)
    {
        $result = $this->http->request(
            'POST',
            'https://api.openai.com/v1/responses',
            ['Authorization: Bearer ' . AIConfig::openAIKey()],
            [
                'model' => AIConfig::chatModel(),
                'instructions' => $instructions,
                'input' => $input,
                'max_output_tokens' => 700,
            ]
        );

        if (isset($result['output_text'])) {
            return trim($result['output_text']);
        }

        $parts = [];
        foreach ($result['output'] ?? [] as $output) {
            foreach ($output['content'] ?? [] as $content) {
                if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) {
                    $parts[] = $content['text'];
                }
            }
        }

        if (!$parts) {
            throw new RuntimeException('Generation response did not contain answer text.');
        }
        return trim(implode("\n", $parts));
    }
}

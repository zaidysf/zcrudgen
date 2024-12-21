<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiGenerator extends BaseGenerator
{
    protected string $apiKey;

    protected string $model;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = config('zcrudgen.ai.api_key');
        $this->model = config('zcrudgen.ai.model', 'gpt-4');
    }

    public function generateBusinessLogic(string $name, array $columns): array
    {
        if (! config('zcrudgen.ai.enabled', false) || empty($this->apiKey)) {
            return [];
        }

        $prompt = $this->buildPrompt($name, $columns);

        try {
            // This can throw GuzzleHttp exceptions
            $response = Http::timeout(30)->throw()->withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert Laravel developer. Generate business logic for a Laravel service class.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('AI API request failed: '.$response->body());
            }

            $content = $response->json('choices.0.message.content');
            if (empty($content)) {
                throw new RuntimeException('Empty response from AI API');
            }

            return $this->parseResponse($content);

        } catch (\Throwable $e) {
            // Log error but don't break the generation process
            logger()->error('AI Generation failed: '.$e->getMessage());

            return [];
        }
    }

    protected function parseResponse(string $response): array
    {
        try {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                throw new RuntimeException('Invalid JSON response structure');
            }

            return $decoded;
        } catch (\JsonException $e) {
            logger()->error('Failed to parse AI response: '.$e->getMessage());

            return [];
        }
    }

    protected function buildPrompt(string $name, array $columns): string
    {
        $columnsList = implode(', ', $columns);

        return <<<PROMPT
        Generate Laravel service class business logic for {$name} model with the following columns:
        {$columnsList}

        Include:
        1. Validation rules
        2. Business logic for create and update operations
        3. Any necessary data transformations
        4. Error handling
        5. Event dispatching if relevant
        6. Cache handling if appropriate
        
        Format the response as JSON with the following structure:
        {
            "methods": {
                "methodName": {
                    "code": "method implementation",
                    "description": "what the method does"
                }
            },
            "traits": ["list of traits to use"],
            "events": ["list of events to create"]
        }
        PROMPT;
    }
}

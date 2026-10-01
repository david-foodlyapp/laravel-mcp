<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiRestaurantAgent
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function reply(array $messages): string
    {
        $apiKey = config('services.openai.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $input = [
            [
                'role' => 'system',
                'content' => 'You are a helpful restaurant assistant. Answer in the language used by the user. Use restaurant tools for factual restaurant data. Never invent restaurant information.',
            ],
            ...$messages,
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $request = [
                'model' => config('services.openai.model'),
                'messages' => $input,
                'tools' => $this->tools(),
            ];

            $providerOnly = config('services.openai.provider_only');

            if (is_string($providerOnly) && $providerOnly !== '') {
                $request['provider'] = [
                    'only' => [$providerOnly],
                    'allow_fallbacks' => false,
                ];
            }

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(12)
                ->post(rtrim((string) config('services.openai.base_url'), '/').'/chat/completions', $request)
                ->throw()
                ->json();

            $message = $response['choices'][0]['message'] ?? [];
            $functionCalls = collect($message['tool_calls'] ?? [])->values();

            if ($functionCalls->isEmpty()) {
                return (string) ($message['content'] ?? 'I could not generate a response.');
            }

            $input[] = $message;

            foreach ($functionCalls as $functionCall) {
                $arguments = json_decode(
                    $functionCall['function']['arguments'] ?? '{}',
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );

                $input[] = [
                    'role' => 'tool',
                    'tool_call_id' => $functionCall['id'],
                    'name' => $functionCall['function']['name'],
                    'content' => $this->callTool($functionCall['function']['name'], $arguments),
                ];
            }
        }

        throw new RuntimeException('The restaurant agent exceeded its tool-call limit.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_restaurants',
                    'description' => 'Search restaurants by name, address, currency, or open status.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => ['string', 'null']],
                            'currency' => ['type' => ['string', 'null']],
                            'open_only' => ['type' => ['boolean', 'null']],
                            'limit' => ['type' => ['integer', 'null']],
                        ],
                        'required' => ['query', 'currency', 'open_only', 'limit'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_restaurant',
                    'description' => 'Get complete details for one restaurant by its external slug.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'slug' => ['type' => 'string'],
                        ],
                        'required' => ['slug'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_open_restaurants',
                    'description' => 'List restaurants marked as currently open.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'currency' => ['type' => ['string', 'null']],
                            'limit' => ['type' => ['integer', 'null']],
                        ],
                        'required' => ['currency', 'limit'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function callTool(string $name, array $arguments): string
    {
        return match ($name) {
            'search_restaurants' => $this->searchRestaurants($arguments),
            'get_restaurant' => $this->getRestaurant($arguments),
            'list_open_restaurants' => $this->listOpenRestaurants($arguments),
            default => json_encode(['error' => 'Unknown restaurant tool.'], JSON_THROW_ON_ERROR),
        };
    }

    /** @param array<string, mixed> $arguments */
    private function searchRestaurants(array $arguments): string
    {
        $restaurants = Restaurant::query()
            ->when($arguments['query'] ?? null, function ($builder, string $query): void {
                $builder->where(function ($builder) use ($query): void {
                    $builder->where('name', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%");
                });
            })
            ->when($arguments['currency'] ?? null, fn ($builder, string $currency) => $builder->where('currency', $currency))
            ->when($arguments['open_only'] ?? false, fn ($builder) => $builder->where('is_open', true))
            ->orderByDesc('is_open')
            ->orderBy('name')
            ->limit(max(1, min((int) ($arguments['limit'] ?? 10), 50)))
            ->get(['external_slug', 'name', 'address', 'price_per_person', 'currency', 'is_open']);

        return json_encode($restaurants->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /** @param array<string, mixed> $arguments */
    private function getRestaurant(array $arguments): string
    {
        $restaurant = Restaurant::where('external_slug', $arguments['slug'] ?? null)->first();

        return json_encode($restaurant?->toArray() ?? ['error' => 'Restaurant not found.'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /** @param array<string, mixed> $arguments */
    private function listOpenRestaurants(array $arguments): string
    {
        $restaurants = Restaurant::query()
            ->where('is_open', true)
            ->when($arguments['currency'] ?? null, fn ($builder, string $currency) => $builder->where('currency', $currency))
            ->orderBy('name')
            ->limit(max(1, min((int) ($arguments['limit'] ?? 20), 50)))
            ->get(['external_slug', 'name', 'address', 'price_per_person', 'currency', 'working_hours']);

        return json_encode($restaurants->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}

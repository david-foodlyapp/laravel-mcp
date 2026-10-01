<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Services\OpenAiRestaurantAgent;
use Illuminate\Support\Facades\Http;

test('restaurant chat page requires authentication', function () {
    $this->get(route('chat'))->assertRedirect(route('login'));
});

test('authenticated users can open the restaurant chat page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('chat'))
        ->assertOk()
        ->assertSee('Restaurant AI');
});

test('restaurant agent executes a restaurant tool and returns the final response', function () {
    config(['services.openai.api_key' => 'test-key']);

    Restaurant::create([
        'external_slug' => 'batumi-one',
        'name' => 'Batumi One',
        'address' => 'Batumi',
        'currency' => 'GEL',
        'is_open' => true,
    ]);

    Http::fakeSequence()
        ->push([
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'tool_calls' => [[
                        'id' => 'call_1',
                        'type' => 'function',
                        'function' => [
                            'name' => 'search_restaurants',
                            'arguments' => json_encode([
                                'query' => 'Batumi',
                                'currency' => null,
                                'open_only' => true,
                                'limit' => 10,
                            ]),
                        ],
                    ]],
                ],
            ]],
        ])
        ->push([
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => 'ვიპოვე Batumi One რესტორანი.',
                ],
            ]],
        ]);

    expect(app(OpenAiRestaurantAgent::class)->reply([
        ['role' => 'user', 'content' => 'მაჩვენე ბათუმის ღია რესტორნები'],
    ]))->toBe('ვიპოვე Batumi One რესტორანი.');

    Http::assertSentCount(2);
});

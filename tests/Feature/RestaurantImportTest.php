<?php

use App\Models\Restaurant;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

test('restaurants are imported across all foodly pagination links', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://api.foodly.dev/api/website/restaurants' => Http::response([
            'data' => [[
                'slug' => 'batumi-one',
                'name' => 'Batumi One',
                'address' => 'Batumi',
                'price_per_person' => '35',
                'working_hours' => '[{"day":"everyday","open":"10:00","close":"22:00"}]',
                'currency' => 'GEL',
                'is_open' => true,
            ]],
            'links' => ['next' => 'https://api.foodly.dev/api/website/restaurants?page=2'],
        ]),
        'https://api.foodly.dev/api/website/restaurants?page=2' => Http::response([
            'data' => [[
                'slug' => 'batumi-two',
                'name' => 'Batumi Two',
                'address' => 'Batumi',
                'price_per_person' => '',
                'working_hours' => '10:00 - 22:00',
                'currency' => 'GEL',
                'is_open' => false,
            ]],
            'links' => ['next' => null],
        ]),
    ]);

    $this->artisan('app:import-restaurants')
        ->expectsOutput('Imported 2 restaurant records.')
        ->assertSuccessful();

    expect(Restaurant::count())->toBe(2);
    expect(Restaurant::where('external_slug', 'batumi-one')->first())
        ->price_per_person->toBe('35.00')
        ->working_hours->toBe([['day' => 'everyday', 'open' => '10:00', 'close' => '22:00']]);

    Http::assertSentCount(2);
});

test('restaurant imports fail when foodly returns an error', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.foodly.dev/api/website/restaurants' => Http::response([], 500),
    ]);

    expect(fn () => $this->artisan('app:import-restaurants'))
        ->toThrow(RequestException::class);

    expect(Restaurant::count())->toBe(0);
});

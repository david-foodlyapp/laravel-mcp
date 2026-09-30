<?php

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\GetRestaurant;
use App\Mcp\Tools\ListOpenRestaurants;
use App\Mcp\Tools\SearchRestaurants;
use App\Models\Restaurant;

test('restaurant tools are registered with the app server', function () {
    AppServer::tools()->assertRegistered([
        SearchRestaurants::class,
        GetRestaurant::class,
        ListOpenRestaurants::class,
    ]);
});

test('restaurant search tool returns matching restaurants', function () {
    Restaurant::create([
        'external_slug' => 'batumi-one',
        'name' => 'Batumi One',
        'address' => 'Batumi',
        'currency' => 'GEL',
        'is_open' => true,
    ]);

    AppServer::tool(SearchRestaurants::class, [
        'query' => 'Batumi',
        'open_only' => true,
    ])->assertSee('batumi-one');
});

test('restaurant details tool returns the requested restaurant', function () {
    Restaurant::create([
        'external_slug' => 'batumi-one',
        'name' => 'Batumi One',
        'address' => 'Batumi',
        'currency' => 'GEL',
        'is_open' => true,
    ]);

    AppServer::tool(GetRestaurant::class, [
        'slug' => 'batumi-one',
    ])->assertSee(['batumi-one', 'Batumi One']);
});

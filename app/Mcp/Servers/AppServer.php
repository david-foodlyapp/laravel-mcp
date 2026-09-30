<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetRestaurant;
use App\Mcp\Tools\ListOpenRestaurants;
use App\Mcp\Tools\SearchRestaurants;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('App Server')]
#[Version('0.0.1')]
#[Instructions('This server provides read-only search and details for restaurants imported from Foodly.')]
class AppServer extends Server
{
    protected array $tools = [
        SearchRestaurants::class,
        GetRestaurant::class,
        ListOpenRestaurants::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}

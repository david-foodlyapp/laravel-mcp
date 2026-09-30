<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetCurrentWeatherTool extends Tool
{
    /**
     * ხელსაწყოს აღწერა AI მოდელისთვის, რათა მიხვდეს როდის გამოიყენოს იგი.
     */
    protected string $description = 'Get the current weather and temperature for a given city.';

    /**
     * იმ პარამეტრების სქემა, რასაც AI გადასცემს ამ ხელსაწყოს.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('The name of the city, e.g. Tbilisi, London')
                ->required(),
        ];
    }

    /**
     * ხელსაწყოს შესრულების ლოგიკა.
     */
    public function handle(Request $request): Response
    {
        $city = $request->get('city');

        // აქ შეიძლება რეალური Weather API-ს გამოძახება (მაგ. Http::get(...))
        // სატესტოდ დავაბრუნოთ მონაცემი:
        $temperature = 22;
        $condition = 'Sunny';

        return Response::text("The current weather in {$city} is {$condition} with a temperature of {$temperature}°C.");
    }
}

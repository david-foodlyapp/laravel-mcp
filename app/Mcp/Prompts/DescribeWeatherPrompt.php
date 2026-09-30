<?php

namespace App\Mcp\Prompts;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;

class DescribeWeatherPrompt extends Prompt
{
    protected string $description = 'Generate a detailed weather summary and outfit recommendation.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('The city to describe weather for')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $city = $request->get('city');

        return Response::text("Please check the weather for {$city}, provide the current conditions, and suggest what to wear today.");
    }
}

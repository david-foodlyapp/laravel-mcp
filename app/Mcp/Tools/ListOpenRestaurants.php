<?php

namespace App\Mcp\Tools;

use App\Models\Restaurant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List Foodly restaurants currently marked as open.')]
class ListOpenRestaurants extends Tool
{
    protected string $description = 'List restaurants that are currently marked as open, optionally filtered by currency.';

    public function handle(Request $request): Response
    {
        $restaurants = Restaurant::query()
            ->where('is_open', true)
            ->when($request->get('currency'), fn ($builder, string $currency) => $builder->where('currency', $currency))
            ->orderBy('name')
            ->limit(max(1, min((int) ($request->get('limit') ?: 20), 50)))
            ->get([
                'external_slug', 'name', 'address', 'price_per_person', 'currency',
                'reservations_enabled', 'working_hours',
            ]);

        return Response::text(json_encode($restaurants->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'currency' => $schema->string()->description('Currency code, for example GEL or AED.'),
            'limit' => $schema->integer()->description('Maximum number of results, from 1 to 50.'),
        ];
    }
}

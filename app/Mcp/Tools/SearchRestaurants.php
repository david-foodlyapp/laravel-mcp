<?php

namespace App\Mcp\Tools;

use App\Models\Restaurant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search Foodly restaurants by name, address, currency, or open status.')]
class SearchRestaurants extends Tool
{
    protected string $description = 'Search restaurants using optional filters and return concise restaurant data.';

    public function handle(Request $request): Response
    {
        $query = Restaurant::query()
            ->when($request->get('query'), function ($builder, string $search): void {
                $builder->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($request->get('currency'), fn ($builder, string $currency) => $builder->where('currency', $currency))
            ->when($request->get('open_only', false), fn ($builder) => $builder->where('is_open', true))
            ->orderByDesc('is_open')
            ->orderBy('name')
            ->limit(max(1, min((int) ($request->get('limit') ?: 10), 50)))
            ->get([
                'external_slug', 'name', 'address', 'image_url', 'price_per_person',
                'currency', 'is_open', 'reservations_enabled', 'working_hours',
            ]);

        return Response::text(json_encode($query->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Restaurant name or address to search for.'),
            'currency' => $schema->string()->description('Currency code, for example GEL or AED.'),
            'open_only' => $schema->boolean()->description('Only return restaurants currently marked as open.'),
            'limit' => $schema->integer()->description('Maximum number of results, from 1 to 50.'),
        ];
    }
}

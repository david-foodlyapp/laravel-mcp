<?php

namespace App\Mcp\Tools;

use App\Models\Restaurant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get complete details for one Foodly restaurant by its slug.')]
class GetRestaurant extends Tool
{
    protected string $description = 'Return complete details for a restaurant identified by its external slug.';

    public function handle(Request $request): Response
    {
        $restaurant = Restaurant::query()
            ->where('external_slug', $request->get('slug'))
            ->first();

        if ($restaurant === null) {
            return Response::text(json_encode([
                'error' => 'Restaurant not found.',
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }

        return Response::text(json_encode($restaurant->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The restaurant external slug, for example restaurant-atlantis.')->required(),
        ];
    }
}

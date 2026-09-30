<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class WeatherGuidelinesResource extends Resource
{
    protected string $uri = 'weather://guidelines';

    protected string $name = 'Weather Guidelines';

    protected string $description = 'General guidelines for weather alerts and clothing advice.';

    protected string $mimeType = 'text/plain';

    public function handle(Request $request): Response
    {
        return Response::text("Weather Guidelines:\n- Above 20°C: Light clothing.\n- 10°C to 20°C: Moderate clothing.\n- Below 10°C: Warm jacket.");
    }
}

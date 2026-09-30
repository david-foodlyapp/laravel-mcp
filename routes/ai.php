<?php

use App\Mcp\Servers\WeatherServer;
use Laravel\Mcp\Facades\Mcp;

// HTTP წვდომისთვის
Mcp::web('/mcp/weather', WeatherServer::class);

// ლოკალური STDIO წვდომისთვის (php artisan mcp:start weather)
Mcp::local('weather', WeatherServer::class);

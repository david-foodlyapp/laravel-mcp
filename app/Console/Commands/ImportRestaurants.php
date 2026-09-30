<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

#[Signature('app:import-restaurants')]
#[Description('Import restaurants from the Foodly website API')]
class ImportRestaurants extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $nextUrl = config('services.foodly.restaurants_url');
        $visitedUrls = [];
        $importedCount = 0;

        while ($nextUrl !== null && ! in_array($nextUrl, $visitedUrls, true)) {
            $visitedUrls[] = $nextUrl;

            $payload = Http::connectTimeout(3)
                ->timeout(10)
                ->retry([100, 500, 1000], 0, function (Throwable $exception): bool {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException
                            && ($exception->response->serverError()
                                || $exception->response->status() === 429));
                })
                ->get($nextUrl)
                ->throw()
                ->json();

            foreach ($payload['data'] ?? [] as $restaurant) {
                if (! isset($restaurant['slug'], $restaurant['name'])) {
                    continue;
                }

                Restaurant::updateOrCreate(
                    ['external_slug' => $restaurant['slug']],
                    [
                        'name' => $restaurant['name'],
                        'address' => $restaurant['address'] ?? null,
                        'logo_url' => $restaurant['logo'] ?? null,
                        'image_url' => $restaurant['image'] ?? null,
                        'price_per_person' => $this->nullableNumeric($restaurant['price_per_person'] ?? null),
                        'discount_rate' => $this->nullableNumeric($restaurant['discount_rate'] ?? null),
                        'latitude' => $this->nullableNumeric($restaurant['latitude'] ?? null),
                        'longitude' => $this->nullableNumeric($restaurant['longitude'] ?? null),
                        'reservations_enabled' => (bool) ($restaurant['reservations_enabled'] ?? false),
                        'working_hours' => $this->decodeWorkingHours($restaurant['working_hours'] ?? null),
                        'currency' => $restaurant['currency'] ?? null,
                        'is_open' => (bool) ($restaurant['is_open'] ?? false),
                        'rank' => $restaurant['rank'] ?? null,
                        'sort_order' => $restaurant['sort_order'] ?? null,
                        'last_synced_at' => now(),
                    ],
                );

                $importedCount++;
            }

            $nextUrl = $payload['links']['next'] ?? null;
        }

        $this->info("Imported {$importedCount} restaurant records.");

        return self::SUCCESS;
    }

    private function nullableNumeric(mixed $value): float|int|null
    {
        return $value === null || $value === '' ? null : $value;
    }

    private function decodeWorkingHours(mixed $workingHours): mixed
    {
        if (! is_string($workingHours) || $workingHours === '') {
            return $workingHours;
        }

        $decoded = json_decode($workingHours, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $workingHours;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $external_slug
 * @property string $name
 * @property string|null $address
 * @property string|null $logo_url
 * @property string|null $image_url
 * @property float|null $price_per_person
 * @property float|null $discount_rate
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool $reservations_enabled
 * @property array<int, array<string, string>>|string|null $working_hours
 * @property string|null $currency
 * @property bool $is_open
 * @property int|null $rank
 * @property int|null $sort_order
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'external_slug',
    'name',
    'address',
    'logo_url',
    'image_url',
    'price_per_person',
    'discount_rate',
    'latitude',
    'longitude',
    'reservations_enabled',
    'working_hours',
    'currency',
    'is_open',
    'rank',
    'sort_order',
    'last_synced_at',
])]
class Restaurant extends Model
{
    protected function casts(): array
    {
        return [
            'price_per_person' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'reservations_enabled' => 'boolean',
            'working_hours' => 'array',
            'is_open' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }
}

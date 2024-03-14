<?php

namespace Codewiser\Notifications\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsWebNotification implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? new WebNotification($value['title'] ?? '', $value['options'] ?? []) : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof WebNotification) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }
}

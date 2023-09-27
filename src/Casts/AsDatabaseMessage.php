<?php

namespace Codewiser\Notifications\Casts;

use Codewiser\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsDatabaseMessage implements CastsAttributes
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

        return is_array($value) ? new DatabaseMessage($value) : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof DatabaseMessage) {
            $value = $value->data;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }
}

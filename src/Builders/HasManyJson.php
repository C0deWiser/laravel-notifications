<?php

namespace Codewiser\Notifications\Builders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class HasManyJson extends HasMany
{
    protected function buildDictionary(Collection $results)
    {
        $foreign = $this->getForeignKeyName();

        /*
         * $foreign msy be as string, as json attribute (one->two->three).
         * In that case $result->{$foreign} will not work,
         * we should extract from array.
         */
        if (str_contains($foreign, '->')) {

            $attribute = str($foreign)->before('->')->toString();
            $path = str($foreign)->after('->')->replace('->', '.')->toString();

            return $results->mapToDictionary(function ($result) use ($attribute, $path) {

                $value = $result->{$attribute};
                $value = $value instanceof Arrayable ? $value->toArray() : $value;
                $key = Arr::get($value, $path);

                return [$this->getDictionaryKey($key) => $result];
            })->all();
        } else {
            return parent::buildDictionary($results);
        }
    }
}
<?php

namespace Savich\Filter\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class Filter
 * @package Savich\Filter\Mixins
 * @method static Builder|\Illuminate\Database\Eloquent\Collection|\Eloquent filter(array $filters = [])
 */
trait Filter
{
    /**
     * @param Builder $query
     * @param array|string $filters
     * @return mixed
     * @throws \Exception
     */
    public function scopeFilter($query, $filters = [])
    {
        if (is_null($filters)) {
            $filters = [];
        }

        if (is_array($filters) || $filters instanceof Collection) {
            $kernel = call_user_func([config('laravel-filter.kernel'), 'instance']);

            return $kernel->filterModel(static::class, $filters, $query);
        }
        throw new \Exception('Filters must be array or Collection type. Got ' . gettype($filters));
    }
}
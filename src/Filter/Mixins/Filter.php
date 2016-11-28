<?php

namespace Savich\Filter\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Filter
 * @package Savich\Filter\Mixins
 * @method static Builder|Collection|\Eloquent filter(array $filters = [])
 */
trait Filter
{
    /**
     * @param Builder $query
     * @param array|string $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters = [])
    {
        $kernel = call_user_func([config('laravel-filter.kernel'), 'instance']);


        return $kernel->filterModel(static::class, $filters, $query);
    }
}
<?php

namespace Savich\Filter\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Savich\Filter\Kernel;

/**
 * Class Filter
 * @package Savich\Filter\Mixins
 * @method static Builder|Collection|\Eloquent filter(Kernel $kernel, array $filters = [])
 */
trait Filter
{
    /**
     * @param Builder $query
     * @param Kernel $kernel
     * @param array|string $filters
     * @return mixed
     */
    public function scopeFilter($query, Kernel $kernel, array $filters = [])
    {
        return $kernel->filterModel(static::class, $filters, $query);
    }
}
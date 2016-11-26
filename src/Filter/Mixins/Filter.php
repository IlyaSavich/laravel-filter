<?php

namespace Savich\Filter\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Savich\Filter\Kernel;

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
        $kernel = Kernel::instance();

        return $kernel->filterModel(static::class, $filters, $query);
    }
}